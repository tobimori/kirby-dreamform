<?php

namespace tobimori\DreamForm\Actions;

use Kirby\Data\Json;
use Kirby\Http\Remote;
use Kirby\Toolkit\A;
use Kirby\Toolkit\V;
use tobimori\DreamForm\DreamForm;

class MailerLiteAction extends Action
{
	public const TYPE = 'mailerlite';

	/**
	 * Returns the Blocks fieldset blueprint for the actions' settings
	 */
	public static function blueprint(): array
	{
		return [
			'name' => t('dreamform.actions.mailerlite.name'),
			'preview' => 'fields',
			'wysiwyg' => true,
			'icon' => 'mailerlite',
			'tabs' => [
				'settings' => [
					'label' => t('dreamform.settings'),
					'fields' => [
						'groups' => [
							'label' => t('dreamform.actions.mailerlite.groups.label'),
							'type' => 'multiselect',
							'options' => A::map(static::getGroups(), fn ($group) => [
								'value' => (string)$group['id'],
								'text' => $group['name']
							])
						],
						'fields' => [
							'label' => t('dreamform.actions.mailerlite.fields.label'),
							'type' => 'object',
							'required' => true,
							'fields' => static::getFieldsBlueprint()
						]
					]
				]
			]
		];
	}

	/**
	 * Create or update the subscriber in MailerLite
	 */
	public function run(): void
	{
		$mapping = $this->block()->content()->get('fields')->toObject();
		$email = $this->submission()->valueForDynamicField($mapping->email())?->value();

		if (!V::email($email)) {
			$this->cancel('dreamform.submission.error.email', public: true);
		}

		$values = [];
		foreach ($mapping->data() as $key => $field) {
			if (!is_string($key) || $key === 'email') {
				continue;
			}

			$value = $this->submission()->valueForDynamicField($mapping->$key())?->value();
			if ($value !== null && $value !== '') {
				$values[$key] = $value;
			}
		}

		$groups = $this->block()->groups()->split();

		try {
			$request = static::request('POST', '/subscribers', [
				'email' => $email,
				'fields' => $values ?: null,
				'groups' => $groups ?: null
			]);
		} catch (\Throwable $e) {
			$this->cancel($e->getMessage());
		}

		if ($request->code() > 299) {
			$this->cancel($request->json()['message'] ?? 'dreamform.submission.error.generic');
		}

		$this->log(
			[
				'template' => [
					'email' => $email
				]
			],
			type: 'none',
			icon: 'mailerlite',
			title: 'dreamform.actions.mailerlite.log.success'
		);
	}

	/**
	 * Returns the available MailerLite fields as DreamForm fields
	 */
	protected static function getFieldsBlueprint(): array
	{
		$fields = [
			'email' => [
				'label' => t('email'),
				'type' => 'dreamform-dynamic-field',
				'required' => true,
				'limitType' => 'email'
			]
		];

		foreach (static::getFields() as $field) {
			$key = $field['key'] ?? null;
			if (!$key || $key === 'email') {
				continue;
			}

			$fields[$key] = [
				'label' => $field['name'] ?? $key,
				'limitType' => ($field['type'] ?? null) === 'number' ? 'number' : null,
				'type' => 'dreamform-dynamic-field'
			];
		}

		return $fields;
	}

	/**
	 * Returns all available subscriber fields
	 */
	protected static function getFields(): array
	{
		$response = static::cache(
			['fields', hash('md5', static::apiKey())],
			fn () => static::request('GET', '/fields?limit=100')->json()
		);

		return $response['data'] ?? [];
	}

	/**
	 * Returns all available groups
	 */
	protected static function getGroups(): array
	{
		$response = static::cache(
			['groups', hash('md5', static::apiKey())],
			fn () => static::request('GET', '/groups?limit=1000')->json()
		);

		return $response['data'] ?? [];
	}

	/**
	 * Get the API key for the MailerLite API
	 */
	protected static function apiKey(): string|null
	{
		return DreamForm::option('actions.mailerlite.apiKey');
	}

	/**
	 * Send a MailerLite API request
	 */
	public static function request(string $method, string $url, array $data = []): Remote
	{
		$params = [
			'headers' => [
				'Accept' => 'application/json',
				'Authorization' => 'Bearer ' . static::apiKey(),
				'User-Agent' => DreamForm::userAgent(),
				'X-Version' => '2026-07-13'
			]
		];

		if ($method !== 'GET') {
			$params['data'] = Json::encode(A::filter($data, fn ($value) => $value !== null));
			$params['headers']['Content-Type'] = 'application/json';
		}

		return Remote::$method("https://connect.mailerlite.com/api{$url}", $params);
	}

	/**
	 * Returns true if the MailerLite action is available
	 */
	public static function isAvailable(): bool
	{
		return !!static::apiKey();
	}

	/**
	 * @inheritDoc
	 */
	public static function group(): string
	{
		return 'newsletter';
	}

	/**
	 * @inheritDoc
	 */
	protected function logSettings(): array|bool
	{
		return [
			'icon' => 'mailerlite',
			'title' => 'dreamform.actions.mailerlite.name'
		];
	}
}
