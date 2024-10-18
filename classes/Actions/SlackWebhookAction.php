<?php

namespace tobimori\DreamForm\Actions;

use tobimori\DreamForm\Actions\Action;
use tobimori\DreamForm\DreamForm;
use Kirby\Http\Remote;
use Kirby\Data\Json;
use Throwable;

/**
 * Action for sending a message in a slack channel.
 */
class SlackWebhookAction extends Action
{
	/**
	 * Returns the Blocks fieldset blueprint for the actions' settings
	 */
	public static function blueprint(): array
	{
		return [
			'title' => t('dreamform.actions.slack.name'),
			'preview' => 'fields',
			'wysiwyg' => true,
			'icon' => 'slack',
			'tabs' => [
				'settings' => [
					'label' => t('settings'),
					'fields' => [
						'webhookUrl' => [
							'label' => 'Webhook URL',
							'type' => 'text',
							'pattern' => 'https:\/\/hooks\.slack\.com\/services\/.+\/.+',
							'placeholder' => 'https://hooks.slack.com/services/...',
							'width' => '1/3',
							'required' => !DreamForm::option('actions.slack.webhook')
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

	/**
	 * Returns the webhook URL
	 */
	protected function webhookUrl(): string
	{
		return $this->block()->webhookUrl()->or(DreamForm::option('actions.slack.webhook'));
	}

	/**
	 * Returns the content to be sent to Slack
	 */
	protected function content(): string
	{
		// get all fields that should be exposed, or use all fields if none are specified
		$exposed = $this->block()->exposedFields()->split();
		if (empty($exposed)) {
			$exposed = $this->form()->fields()->keys();
		}

		// get the values & keys of the exposed fields
		$content = t('dreamform.actions.slack.message') . $this->form()->title() . "\n\n\n";
		foreach ($exposed as $fieldId) {
			$field = $this->form()->fields()->find($fieldId);
			$value = $this->submission()->valueForId($fieldId);

			if ($field && $value?->isNotEmpty()) {
				// add the field key and the value to the webhook content
				$content .= "*{$field->label()}*\n>{$value}\n\n";
			}
		}

		return $content;
	}

	/**
	 * Run the action
	 */
	public function run(): void
	{
		try {
			$request = Remote::post($this->block()->webhookUrl()->value(), [
				'headers' => [
					'User-Agent' => DreamForm::userAgent(),
					'Content-Type' => 'application/json'
				],
				'data' => Json::encode([
					'text' => $this->content(),
					'mrkdwn' => true
				])
			]);

			if ($request->code() > 299) {
				$this->cancel();
			}
		} catch (Throwable $e) {
			$this->cancel($e->getMessage());
		}

		if ($request->code() > 299) {
			$this->cancel('dreamform.actions.slack.log.error');
		}

		$this->log([], type: 'none', icon: 'slack', title: 'dreamform.actions.slack.log.success');
	}

	/**
	 * Returns the actions' blueprint group
	 */
	public static function group(): string
	{
		return 'notifications';
	}

	/**
	 * Returns the base log settings for the action
	 */
	protected function logSettings(): array|bool
	{
		return [
			'icon' => 'slack',
			'title' => 'dreamform.actions.slack.name'
		];
	}
}
