<?php

namespace tobimori\DreamForm\Actions;

use Kirby\Cms\App;
use Kirby\Data\Json;
use Kirby\Http\Remote;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;
use Kirby\Toolkit\V;
use tobimori\DreamForm\DreamForm;

/**
 * Action for subscribing a user to a Buttondown newsletter list.
 * Docs: https://api.buttondown.email/v1/docs
 */
class ButtondownAction extends Action
{
	/**
	 * Returns whether the Buttondown simple mode is enabled
	 * Simple mode supports free plans, rmoves tags support/sending reminders
	 */
	protected static function simpleMode(): bool
	{
		return DreamForm::option('actions.buttondown.simpleMode') === true;
	}

	/**
	 * Returns the Blocks fieldset blueprint for the actions' settings
	 */
	public static function blueprint(): array
	{
		return [
			'name' => t('dreamform.actions.buttondown.name'),
			'preview' => 'fields',
			'wysiwyg' => true,
			'icon' => 'buttondown',
			'tabs' => [
				'settings' => [
					'label' => t('dreamform.settings'),
					'fields' => A::merge([
						'emailField' => [
							'label' => t('dreamform.actions.buttondown.emailField.label'),
							'required' => true,
							'extends' => 'dreamform/fields/field',
							'width' => '1/3'
						],
						'exposeMetadata' => [
							'label' => t('dreamform.actions.buttondown.exposeMetadata.label'),
							'extends' => 'dreamform/fields/field',
							'type' => 'multiselect',
							'width' => '2/3'
						],
					], static::tagsBlueprint())
				]
			]
		];
	}

	/**
	 * Returns the tag assignment part of the panel blueprint
	 */
	protected static function tagsBlueprint(): array
	{
		if (static::simpleMode()) {
			return [];
		}

		return [
			'line' => true,
			'tags' => [
				'label' => t('dreamform.actions.buttondown.tags.label'),
				'extends' => 'dreamform/fields/static-dynamic-toggles',
				'width' => '1/3'
			],
			'tagsField' => [
				'label' => ' ',
				'extends' => 'dreamform/fields/field',
				'width' => '2/3',
				'when' => [
					'tags' => 'field'
				]
			],
			'tagsStatic' => [
				'label' => ' ',
				'type' => 'multiselect',
				'width' => '2/3',
				'options' => A::map(static::tags(), fn ($tag) => [
					'text' => $tag['name'],
					'value' => $tag['id']
				]),
				'when' => [
					'tags' => 'static'
				]
			]
		];
	}

	/**
	 * Get tags for the submission
	 */
	protected function submissionTags(): array|null
	{
		if (static::simpleMode()) {
			return null;
		}

		$tags = [];
		if ($this->block()->tags()->value() === 'field') {
			$tags = $this->submission()->valueForId($this->block()->tagsField()->value())->value();
		} else {
			$tags = $this->block()->tagsStatic()->value();
		}

		if (is_string($tags)) {
			$tags = Str::split($tags, ',');
		}

		return count($tags) > 0 ? $tags : null;
	}

	/**
	 * Get exposed fields for metadata from the submission
	 */
	protected function metadata(): array|null
	{
		$metadata = [];
		foreach ($this->block()->exposeMetadata()->split() as $fieldId) {
			$field = $this->form()->fields()->find($fieldId);
			$metadata[$field->key()] = $this->submission()->valueForId($fieldId)->value();
		}

		return count($metadata) > 0 ? $metadata : null;
	}

	/**
	 * Subscribe the user to the Buttondown email list
	 */
	public function run(): void
	{
		// check if email is valid
		$emailField = $this->block()->emailField()->value();
		$email = $this->submission()->valueForId($emailField)?->value();
		if (!$email) {
			return;
		}

		if (!V::email($email)) {
			$this->cancel('dreamform.submission.error.email', public: true);
		}

		// collect data for the request
		$data = A::filter([
			'email' => $email,
			'metadata' => static::metadata(),
			'tags' => static::submissionTags(),
			'referrer_url' => $this->submission()->referer(),
		], fn ($value) => $value !== null);

		$logData = ['template' => ['email' => $email]];

		// subscribe the user
		$subscribeRequest = static::request('POST', '/subscribers', A::merge($data, A::filter([
			// utm fields can only be assigned on creation
			'utm_campaign' => $this->submission()->valueFor('utm_campaign')?->value(),
			'utm_medium' => $this->submission()->valueFor('utm_medium')?->value(),
			'utm_source' => $this->submission()->valueFor('utm_source')?->value()
		], fn ($value) => $value !== null)));

		// some error occurred, check for next steps
		if ($subscribeRequest->code() !== 201) {
			// update subscriber data if email already exists
			if (!static::simpleMode() && $subscribeRequest->json()['code'] === 'email_already_exists') {
				$updateRequest = static::request('PATCH', "/subscribers/{$email}", array_filter($data, fn ($key) => $key !== 'email', ARRAY_FILTER_USE_KEY));

				// send reminder if subscriber is unactivated
				if ($updateRequest->json()['subscriber_type'] === 'unactivated') {
					$reminderRequest = static::request('POST', "/subscribers/{$email}/send-reminder");
					if ($reminderRequest->code() !== 200) {
						$this->cancel($reminderRequest->json()['detail']);
					}

					$this->log(icon: 'buttondown', title: 'dreamform.actions.buttondown.log.reminder', data: $logData, type: 'none');
					return;
				}

				$this->log(icon: 'buttondown', title: 'dreamform.actions.buttondown.log.alreadySubscribed', data: $logData, type: 'none');
				return;
			}

			// some other error occurred, cancel the submission
			$this->cancel($data['detail']);
		}

		// everything went well
		$this->log(icon: 'buttondown', title: 'dreamform.actions.buttondown.log.success', data: $logData, type: 'none');
	}

	/**
	 * Get all tags from Buttondown
	 */
	protected static function tags(): array
	{
		return static::cache('tags', function () {
			$request = static::request('GET', '/tags');
			if ($request->code() !== 200) {
				return [];
			}

			$tags = $request->json();
			return $tags['results'];
		});
	}

	/**
	 * Get the API URL
	 */
	protected static function apiUrl(): string
	{
		return "https://api.buttondown.email/v1";
	}

	/**
	 * Make a request to the Buttondown API
	 */
	protected static function request(string $method, string $url, array $data = []): Remote
	{
		$apiKey = DreamForm::option('actions.buttondown.apiKey');

		return Remote::$method(static::apiUrl() . $url, A::merge(
			[
				'headers' => [
					'User-Agent' => DreamForm::userAgent(),
					'Authorization' => "Token {$apiKey}",
					'Content-Type' => 'application/json'
				]
			],
			$method !== 'GET' ? [
				'data' => Json::encode($data),
			] : []
		));
	}

	/**
	 * Check if the Buttondown API is available
	 */
	public static function isAvailable(): bool
	{
		$apiToken = DreamForm::option('actions.buttondown.apiKey');
		if (!$apiToken) {
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

	/**
	 * Returns the base log settings for the action
	 */
	protected function logSettings(): array|bool
	{
		return [
			'icon' => 'buttondown',
			'title' => 'dreamform.actions.buttondown.name'
		];
	}
}
