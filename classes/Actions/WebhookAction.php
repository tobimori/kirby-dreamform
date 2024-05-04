<?php

namespace tobimori\DreamForm\Actions;

use Kirby\Http\Remote;
use Kirby\Http\Url;
use Throwable;

/**
 * Action for sending webhooks, e.g. for use with Zapier.
 */
class WebhookAction extends Action
{
	public static function blueprint(): array
	{
		return [
			'name' => t('dreamform.actions.webhook.name'),
			'preview' => 'fields',
			'wysiwyg' => true,
			'icon' => 'webhook',
			'tabs' => [
				'settings' => [
					'label' => t('dreamform.settings'),
					'fields' => [
						'webhookUrl' => [
							'label' => 'dreamform.actions.webhook.url.label',
							'type' => 'url',
							'placeholder' => 'https://hooks.zapier.com/hooks/catch/...',
							'width' => '1/3',
							'required' => true
						],
						'exposedFields' => [
							'label' => 'dreamform.actions.webhook.exposedFields.label',
							'extends' => 'dreamform/fields/field',
							'type' => 'multiselect',
							'width' => '2/3'
						]
					]
				]
			]
		];
	}

	public function run(): void
	{
		// get all fields that should be exposed, or use all fields if none are specified
		$exposed = $this->block()->exposedFields()->split();
		if (empty($exposed)) {
			$exposed = $this->form()->fields()->keys();
		}

		// get the values & keys of the exposed fields
		$content = [];
		foreach ($exposed as $fieldId) {
			$field = $this->form()->fields()->find($fieldId);
			$value = $this->submission()->valueForId($fieldId);

			if ($field && $value?->isNotEmpty()) {
				// add the field key and the value to the webhook content
				$content[$field->key()] = $value->value();
			}
		}

		// send the webhook
		try {
			$request = Remote::post($this->block()->webhookUrl()->value(), [
				'headers' => [
					'User-Agent' => 'Kirby DreamForm',
					'Content-Type' => 'application/json'
				],
				'data' => json_encode($content)
			]);
		} catch (Throwable $e) {
			// (this will only be shown in the frontend if debug mode is enabled)
			$this->cancel($e->getMessage());
		}

		if ($request->code() > 299) {
			$this->cancel('dreamform.actions.webhook.log.error');
		}

		$this->log([
			'template' => [
				'url' => Url::toObject($request->url())->domain()
			]
		], type: 'none', icon: 'webhook', title: 'dreamform.actions.webhook.log.success');
	}

	/**
	 * Returns the base log settings for the action
	 */
	protected function logSettings(): array|bool
	{
		return [
			'icon' => 'webhook',
			'title' => 'dreamform.actions.webhook.name'
		];
	}
}
