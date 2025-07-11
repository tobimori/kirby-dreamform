<?php

namespace tobimori\DreamForm\Actions;

use Kirby\Data\Json;
use Kirby\Http\Remote;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;
use Kirby\Toolkit\V;
use tobimori\DreamForm\DreamForm;
use tobimori\DreamForm\Models\FormPage;

class BrevoAction extends Action
{
	public const TYPE = 'brevo';

	/**
	 * Returns the Blocks fieldset blueprint for the actions' settings
	 */
	public static function blueprint(): array
	{
		return [
			'name' => t('dreamform.actions.brevo.name'),
			'preview' => 'fields',
			'wysiwyg' => true,
			'icon' => 'brevo',
			'tabs' => [
				'settings' => [
					'label' => t('dreamform.settings'),
					'fields' => [
						'list' => [
							'label' => t('dreamform.actions.brevo.list.label'),
							'type' => 'select',
							'options' => A::reduce(static::getLists(), fn ($prev, $list) => A::merge($prev, [
								"id-{$list['id']}" => $list['name']
							]), []),
							'required' => true
						],
						'doubleOptIn' => [
							'label' => t('dreamform.actions.brevo.doubleOptIn.label'),
							'type' => 'toggle',
							'width' => '1/2',
							'help' => t('dreamform.actions.brevo.doubleOptIn.help')
						],
						'doubleOptInTemplate' => [
							'label' => t('dreamform.actions.brevo.doubleOptInTemplate.label'),
							'type' => count(static::getTemplates()) > 0 ? 'select' : 'info',
							'width' => '1/2',
							'required' => count(static::getTemplates()) > 0,
							'options' => A::reduce(static::getTemplates(), fn ($prev, $template) => A::merge($prev, [
								"id-{$template['id']}" => $template['name']
							]), []),
							'help' => count(static::getTemplates()) > 0
								? t('dreamform.actions.brevo.doubleOptInTemplate.help')
								: t('dreamform.actions.brevo.doubleOptInTemplate.empty'),
							'when' => [
								'doubleOptIn' => true
							]
						],
						'doubleOptInRedirect' => [
							'label' => t('dreamform.actions.brevo.doubleOptInRedirect.label'),
							'type' => 'link',
							'width' => '1/2',
							'required' => true,
							'help' => t('dreamform.actions.brevo.doubleOptInRedirect.help'),
							'options' => [
								'url',
								'page',
								'file'
							],
							'when' => [
								'doubleOptIn' => true
							]
						],
						'attributes' => [
							'label' => t('dreamform.actions.brevo.attributes.label'),
							'help' => t('dreamform.actions.brevo.attributes.help'),
							'type' => 'object',
							'required' => true,
							'empty' => t('dreamform.actions.brevo.attributes.empty'),
							'fields' => static::getAttributeFields()
						],
					]
				]
			]
		];
	}

	/**
	 * Subscribe the user to the Brevo list
	 */
	public function run(): void
	{
		$list = $this->block()->list()->value();
		$mapping = $this->block()->attributes()->toObject();

		// get the email address from the submission
		$email = $this->submission()->valueForDynamicField($mapping->email())?->value();
		if (!$email) {
			return;
		}

		if (!V::email($email)) {
			$this->cancel('dreamform.submission.error.email', public: true);
		}

		// get data for merge fields from the submission
		$attributes = [];
		foreach ($mapping->data() as $attribute => $fieldId) {
			if ($attribute === 'email' || !$fieldId) {
				continue;
			}

			if ($value = $this->submission()->valueForDynamicField($fieldId)?->value()) {
				$attributes[Str::upper($attribute)] = $value;
			}
		}

		// subscribe or update the user
		$doubleOptIn = $this->block()->doubleOptIn()->toBool();
		$request = static::request('POST', $doubleOptIn ? "/contacts/doubleOptinConfirmation" : "/contacts", [
			'email' => $email,
			'attributes' => (object) $attributes,
			($doubleOptIn ? 'includeListIds' : 'listIds') => [intval(Str::replace($list, 'id-', ''))],
			'templateId' => $doubleOptIn ? intval(Str::replace($this->block()->doubleOptInTemplate()->value(), 'id-', '')) : null,
			'redirectionUrl' => $doubleOptIn ? $this->block()->doubleOptInRedirect()->toUrl() : null,
		]);

		if ($request->code() > 299) {
			$this->cancel($request->json()['message'] ?? "dreamform.submission.error.email");
		}

		$this->log(
			[
				'template' => [
					'email' => $email,
					'list' => A::find(static::getLists(), fn ($entry) => $entry['id'] === $list)['name']
				]
			],
			type: 'none',
			icon: 'brevo',
			title: 'dreamform.actions.brevo.log.success'
		);
	}

	/**
	 * Returns an array of available lists in the Brevo account
	 */
	protected static function getLists(): array
	{
		$response = static::cache(
			'lists',
			fn () => static::request('GET', '/contacts/lists')?->json()
		);

		return $response['lists'] ?? [];
	}

	/**
	 * Returns an array of available templates in the Brevo account
	 */
	protected static function getTemplates(): array
	{
		$response = static::cache(
			'templates',
			fn () => static::request('GET', '/smtp/templates?limit=1000')?->json()
		);

		return $response['templates'] ?? [];
	}


	/**
	 * Returns the available attributes as fields for the field mapping object
	 */
	protected static function getAttributeFields(): array
	{
		$response = static::cache(
			'attributes',
			fn () => static::request('GET', '/contacts/attributes')?->json()
		);

		$attributes = $response['attributes'] ?? [];


		$fields = [
			'email' => [
				'label' => t('email'),
				'type' => 'dreamform-dynamic-field',
				'required' => true,
				'limitType' => 'email'
			]
		];

		foreach ($attributes as $attribute) {
			// skip calculated attributes
			if (isset($attribute['calculatedValue'])) {
				continue;
			}

			if (isset($attribute['type']) && $attribute['type'] === 'text') {
				$fields[$attribute['name']] = [
					'label' => $attribute['name'],
					'type' => 'dreamform-dynamic-field'
				];
			}
		}

		return $fields;
	}

	/**
	 * Get the API key for the Brevo API
	 **/
	protected static function apiKey(): string|null
	{
		return DreamForm::option('actions.brevo.apiKey');
	}

	/**
	 * Send a Brevo API request
	 */
	public static function request(string $method, string $url, array $data = []): Remote
	{
		if ($method !== 'GET') {
			$params = [
				'data' => Json::encode(A::filter($data, fn ($value) => $value !== null)),
				'headers' => [
					'Content-Type' => 'application/json',
				]
			];
		}

		return Remote::$method('https://api.brevo.com/v3/' . $url, A::merge(
			$params ?? [],
			[
				'headers' => [
					'Accept' => 'application/json',
					'Api-Key' => static::apiKey()
				]
			]
		));
	}

	/**
	 * Returns true if the Brevo action is available
	 */
	public static function isAvailable(): bool
	{
		return !!static::apiKey();
	}

	/**
	 * Returns the actions' blueprint group
	 */
	public static function group(): string
	{
		return 'newsletter';
	}

	/**
	 * Returns the base log settings for the action
	 */
	protected function logSettings(): array|bool
	{
		return [
			'icon' => 'brevo',
			'title' => 'dreamform.actions.brevo.name'
		];
	}
}
