<?php

namespace tobimori\DreamForm\Actions;

use Kirby\Cms\App;
use Kirby\Data\Json;
use Kirby\Form\Form;
use Kirby\Http\Remote;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;
use tobimori\DreamForm\Models\FormPage;

class MailchimpAction extends Action
{
	/**
	 * Returns the Blocks fieldset blueprint for the actions' settings
	 */
	public static function blueprint(): array
	{
		$lists = static::cache(
			'lists',
			fn () => static::request('GET', '/lists')?->json()
		);

		return [
			'title' => t('dreamform.mailchimp-action'),
			'preview' => 'fields',
			'wysiwyg' => true,
			'icon' => 'mailchimp',
			'tabs' => [
				'settings' => [
					'label' => t('dreamform.settings'),
					'fields' => [
						'list' => [
							'label' => t('dreamform.mailchimp-list'),
							'type' => 'select',
							'options' => A::reduce($lists['lists'], fn ($prev, $list) => A::merge($prev, [
								$list['id'] => $list['name']
							]), []),
							'width' => '2/3',
							'required' => true
						],
						'doubleOptIn' => [
							'label' => t('dreamform.double-opt-in'),
							'type' => 'toggle',
							'width' => '1/3',
							'help' => t('dreamform.double-opt-in-help')
						],
						'fieldMapping' => [
							'label' => t('dreamform.mailchimp-field-mapping'),
							'help' => t('dreamform.mailchimp-field-mapping-help'),
							'type' => 'dreamform-api-object',
							'required' => true,
							'sync' => 'list',
							'empty' => t('dreamform.mailchimp-field-mapping-empty'),
							'api' => 'mailchimp'
						],
					]
				]
			]
		];
	}

	/**
	 * Subscribe the user to the Mailchimp list
	 */
	public function run(): void
	{
		$list = $this->block()->list()->value();
		$mapping = $this->block()->fieldMapping()->toObject();

		// get the email address from the submission
		$email = $this->submission()->valueForId($mapping->emailAddress()->value())->value();
		if (!$email) {
			return;
		}

		// get data for merge fields from the submission
		$mergeFields = [];
		foreach ($mapping->data() as $mergeField => $fieldId) {
			if (A::has(['emailaddress', 'tags', 'tagsfield', 'tagsstatic'], $mergeField) || !$fieldId) {
				continue;
			}

			if ($value = $this->submission()->valueForId($fieldId)?->value()) {
				$mergeFields[Str::upper($mergeField)] = $value;
			}
		}

		// get tags from submission
		$tags = A::map(
			($mapping->tags()->value() === 'static' ?
				$mapping->tagsStatic()->value() :
				$this->submission()->valueForId($mapping->tagsField()->value())?->split()) ?? [],
			fn ($tag) => isset(static::getTags($list)[$tag]) ? static::getTags($list)[$tag] : $tag
		);

		// subscribe or update the user
		$hash = md5(strtolower($email));
		$request = static::request('PUT', "/lists/{$list}/members/{$hash}?skip_merge_validation=true", [
			'email_address' => $email,
			'status_if_new' => $this->block()->doubleOptIn()->toBool() ? 'pending' : 'subscribed',
			'merge_fields' => $mergeFields,
			'tags' => $tags,

			// this will be included if it's already set (and enabled in the config)
			'ip_signup' => $this->submission()->metadata()->ip()?->value(),
			'language' => App::instance()->languageCode()
		]);

		if ($request->code() === 200) {
			ray($request->json());
			$this->log([
				'text' => 'dreamform.mailchimp-subscribed-log'
			], icon: 'mailchimp', title: 'dreamform.mailchimp');
		}
	}

	/**
	 * Returns an array of static segments/tags for the list
	 */
	protected static function getTags(string $list): array
	{
		// Retrieve segment tags from the API
		$segments = static::cache(
			"{$list}.segments",
			fn () => static::request('GET', "/lists/{$list}/segments?count=1000")?->json()
		)['segments'];

		// Create an array of segment tags
		$tags = A::reduce($segments, fn ($prev, $segment) => A::merge(
			$prev,
			$segment['type'] === 'static' ? [
				"id_{$segment['id']}" => $segment['name']
			] : []
		), []);

		return $tags;
	}

	/**
	 * Returns the available merge fields for the list
	 * from the mailchimp API
	 */
	public static function fieldMapping(FormPage $page, string $list): array
	{
		// Retrieve merge fields from the API
		$mergeFields = static::cache(
			"{$list}.fields",
			fn () => static::request('GET', "/lists/{$list}/merge-fields")?->json()
		);

		// Create the base blueprint with the email address field
		$blueprint = [
			'emailAddress' => [
				'label' => t('email'),
				'type' => 'select',
				'required' => true,
				'options' => $options = FormPage::getFields(),
			],
		];

		// Add supported merge fields to the blueprint
		foreach ($mergeFields['merge_fields'] as $mergeField) {
			if (!A::has(['address', 'birthday', 'date', 'imageurl'], $mergeField['type'])) {
				$blueprint[Str::lower($mergeField['tag'])] = [
					'label' => "{$mergeField['name']} ({$mergeField['tag']})",
					'type' => 'select',
					'options' => $options,
				];
			}
		}

		// Add tags-related fields to the blueprint
		$blueprint = A::merge($blueprint, [
			'line' => [
				'type' => 'line'
			],
			'tags' => [
				'label' => t('dreamform.assign-tags-to-subscriber'),
				'type' =>  'toggles',
				'required' => true,
				'default' => 'static',
				'options' => [
					'static' => t('dreamform.static'),
					'field' => t('dreamform.from-field')
				],
				'width' => '1/3'
			],
			'tagsField' => [
				'label' => t('dreamform.tags'),
				'type' => 'select',
				'options' => $options,
				'width' => '2/3',
				'when' => [
					'tags' => 'field'
				]
			],
			'tagsStatic' => [
				'label' => t('dreamform.tags'),
				'type' => 'multiselect',
				'options' => static::getTags($list),
				'width' => '2/3',
				'when' => [
					'tags' => 'static'
				]
			]
		]);

		// Create and return the form fields based on the blueprint and page model
		return (new Form([
			'fields' => $blueprint,
			'model' => $page
		]))->fields()->toArray();
	}
	/**
	 * Get the API key for the Mailchimp API
	 **/
	public static function apiKey(): string|null
	{
		$option = App::instance()->option('tobimori.dreamform.actions.mailchimp.apiKey');
		if (is_callable($option)) {
			return $option();
		}

		return $option;
	}

	/**
	 * Send a Mailchimp API request
	 */
	public static function request(string $method, string $url, array $data = []): Remote
	{
		if ($method !== 'GET') {
			$params = [
				'data' => Json::encode(A::filter($data, fn ($value) => $value !== null)),
				'headers' => [
					'Content-Type' => 'application/json'
				]
			];
		}

		return Remote::$method(static::apiUrl() . $url, A::merge([
			'basicAuth' => "dreamform:" . static::apiKey(),
		], $params ?? []));
	}

	/**
	 * Get the Mailchimp API URL
	 */
	public static function apiUrl(): string
	{
		$dc = Str::after(static::apiKey(), '-');
		return "https://{$dc}.api.mailchimp.com/3.0";
	}

	/**
	 * Returns true if the Mailchimp action is available
	 */
	public static function isAvailable(): bool
	{
		if (!static::apiKey()) {
			return false;
		}

		return static::cache('ping', function () {
			$request = static::request('GET', '/ping');
			return $request->code() === 200;
		});
	}

	/**
	 * Returns the actions' blueprint group
	 */
	public static function group(): string
	{
		return 'newsletter';
	}
}
