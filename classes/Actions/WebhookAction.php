<?php

namespace tobimori\DreamForm\Actions;

use Kirby\Http\Remote;

/**
 * Action for sending webhooks, e.g. for use with Zapier.
 *
 * @package tobimori\DreamForm\Actions
 */
class WebhookAction extends Action
{
	public static function blueprint(): array
	{
		return [
			'title' => t('dreamform.webhook-action'),
			'preview' => 'fields',
			'wysiwyg' => true,
			'icon' => 'webhook',
			'tabs' => [
				'settings' => [
					'label' => t('dreamform.settings'),
					'fields' => [
						'webhookUrl' => [
							'label' => 'dreamform.webhook-url',
							'type' => 'url',
							'placeholder' => 'https://hooks.zapier.com/hooks/catch/...',
							'width' => '1/3',
							'required' => true
						],
						'exposedFields' => [
							'label' => 'dreamform.exposed-fields',
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

			if ($field && $value) {
				// add the field key and the value to the webhook content
				$content[$field->key()] = $value->value();
			}
		}

		// send the webhook
		$request = Remote::post($this->block()->webhookUrl()->value(), [
			'headers' => [
				'User-Agent' => 'Kirby DreamForm',
				'Content-Type' => 'application/json'
			],
			'data' => json_encode($content)
		]);

		// silently abort if the request was not successful
		// (this will only be shown in the frontend if debug mode is enabled)
		if ($request->code() !== 200) {
			$this->error(t('dreamform.webhook-error'));
		}
	}
}
