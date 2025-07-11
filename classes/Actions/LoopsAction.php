<?php

namespace tobimori\DreamForm\Actions;

use Kirby\Data\Json;
use Kirby\Http\Remote;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;
use Kirby\Toolkit\V;
use tobimori\DreamForm\DreamForm;

class LoopsAction extends Action
{
	public const TYPE = 'loops';

	/**
	 * Returns the Blocks fieldset blueprint for the actions' settings
	 */
	public static function blueprint(): array
	{
		return [
			'name' => t('dreamform.actions.loops.name'),
			'preview' => 'fields',
			'wysiwyg' => true,
			'icon' => 'loops',
			'tabs' => [
				'settings' => [
					'label' => t('dreamform.settings'),
					'fields' => [
						'lists' => [
							'label' => t('dreamform.actions.loops.lists.label'),
							'type' => 'multiselect',
							'options' => A::reduce(static::getLists(), fn ($prev, $list) => A::merge($prev, [
								$list['id'] => $list['name']
							]), []),
							'help' => t('dreamform.actions.loops.lists.help'),
							'width' => '2/3',
						],
						'subscribed'	=> [
							'label' => t('dreamform.actions.loops.subscribed.label'),
							'help' => t('dreamform.actions.loops.subscribed.help'),
							'type' => 'toggles',
							'default' => '',
							'width' => '1/3',
							'options' => [
								'' => t('dreamform.actions.loops.subscribed.unset'),
								'true' => t('dreamform.actions.loops.subscribed.yes'),
								'false' => t('dreamform.actions.loops.subscribed.no'),
							]
						],
						'fields' => [
							'label' => t('dreamform.actions.loops.fields.label'),
							'type' => 'object',
							'required' => true,
							'fields' => static::getFieldsBlueprint(),
							'default' => [null]
						]
					]
				]
			]
		];
	}

	/**
	 * Add the user to your contacts in Loops
	 */
	public function run(): void
	{
		$fields = $this->block()->fields()->toObject();
		$email = $this->submission()->valueForDynamicField($fields->email())?->value();

		if (!V::email($email)) {
			$this->cancel('dreamform.submission.error.email', public: true);
		}

		// Loops API expects the field keys with same casing
		// kirby always stores the field keys in lowercase
		$casedKeys = array_keys(static::getFieldsBlueprint());
		$values = [];
		foreach ($fields->data() as $key => $field) {
			if ($key === 'email') {
				continue;
			}

			// get the field value using the field object method
			$value = $this->submission()->valueForDynamicField($fields->$key())?->value();

			if ($value) {
				$casedKey = A::find($casedKeys, fn ($k) => Str::lower($k) === Str::lower($key));
				if (!$casedKey) {
					continue;
				}
				$values[$casedKey] = $value;
			}
		}


		// get data for merge fields from the submission
		$mailingLists = [];
		foreach ($this->block()->lists()->split() as $id) {
			$mailingLists[$id] = true;
		}

		// subscribe or update the user
		$request = static::request('PUT', "/contacts/update", array_merge([
			'email' => $email,
			'subscribed' => $this->block()->subscribed()->toBool() ?? null,
			'mailingLists' => $mailingLists
		], $values));

		if ($request->code() > 299) {
			$this->cancel($request->json()['message'] ?? "dreamform.submission.error.email");
		}

		$this->log(
			[
				'template' => [
					'email' => $email,
				]
			],
			type: 'none',
			icon: 'loops',
			title: 'dreamform.actions.loops.log.success'
		);
	}

	protected static function getFieldsBlueprint(): array
	{
		$fields = [
			'email' => [
				'label' => t('email'),
				'type' => 'dreamform-dynamic-field',
				'required' => true,
			],
			'firstName' => [
				'label' => t('dreamform.common.firstName'),
				'type' => 'dreamform-dynamic-field',
			],
			'lastName' => [
				'label' => t('dreamform.common.lastName'),
				'type' => 'dreamform-dynamic-field',
			],
			'source' => [
				'label' => t('dreamform.common.source'),
				'type' => 'dreamform-dynamic-field',
			],
			'userGroup' => [
				'label' => t('dreamform.actions.loops.userGroup.label'),
				'help' => t('dreamform.actions.loops.userGroup.help'),
				'type' => 'dreamform-dynamic-field',
			],
			'userId' => [
				'label' => t('dreamform.actions.loops.userId.label'),
				'help' => t('dreamform.actions.loops.userId.help'),
				'type' => 'dreamform-dynamic-field',
			]
		];

		foreach (
			static::cache(
				'customfields',
				fn () => static::request('GET', '/contacts/customFields')->json()
			) as $field
		) {
			if ($field['type'] === 'date' || $field['type'] === 'boolean') {
				continue;
			}

			$fields[$field['key']] = [
				'label' => $field['label'],
				'limitType' => $field['type'] === 'number' ? 'number' : null,
				'type' => 'dreamform-dynamic-field',
			];
		}

		return $fields;
	}

	/**
	 * Returns an array of available lists in the Loops account
	 */
	protected static function getLists(): array
	{
		return static::cache(
			'lists',
			fn () => static::request('GET', '/lists')?->json()
		);
	}

	/**
	 * Get the API key for the Loops API
	 **/
	protected static function apiKey(): string|null
	{
		return DreamForm::option('actions.loops.apiKey');
	}

	/**
	 * Send a Loops API request
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

		return Remote::$method("https://app.loops.so/api/v1" . $url, A::merge(
			$params ?? [],
			[
				'headers' => [
					'Accept' => 'application/json',
					'Authorization' => 'Bearer ' . static::apiKey()
				]
			]
		));
	}


	/**
	 * Returns true if the Loops action is available
	 */
	public static function isAvailable(): bool
	{
		if (!static::apiKey()) {
			return false;
		}

		return static::cache(
			['api-key', hash('md5', static::apiKey())],
			fn () => static::request('GET', '/api-key')?->json()
		)["success"] === true;
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
			'icon' => 'loops',
			'title' => 'dreamform.actions.loops.name'
		];
	}
}
