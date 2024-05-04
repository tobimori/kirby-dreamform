<?php

namespace tobimori\DreamForm\Actions;

use Kirby\Cms\App;
use Kirby\Data\Json;
use Kirby\Http\Remote;
use Throwable;
use tobimori\DreamForm\DreamForm;

/**
 * Action for sending a message in a discord channel.
 */
class DiscordWebhookAction extends Action
{
	/**
	 * Returns the Blocks fieldset blueprint for the actions' settings
	 */
	public static function blueprint(): array
	{
		return [
			'name' => t('dreamform.actions.discord.name'),
			'preview' => 'fields',
			'wysiwyg' => true,
			'icon' => 'discord',
			'tabs' => [
				'settings' => [
					'label' => t('settings'),
					'fields' => [
						'webhookUrl' => [
							'label' => 'dreamform.actions.webhook.url.label',
							'type' => 'text',
							'pattern' => 'https:\/\/discord\.com\/api\/webhooks\/.+\/.+',
							'placeholder' => 'https://discord.com/api/webhooks/...',
							'width' => '1/3',
							'required' => !DreamForm::option('actions.discord.webhook')
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
		return $this->block()->webhookUrl()->or(DreamForm::option('actions.discord.webhook'));
	}

	/**
	 * Returns the content to be sent to Discord
	 */
	protected function content(): string
	{
		// get all fields that should be exposed, or use all fields if none are specified
		$exposed = $this->block()->exposedFields()->split();
		if (empty($exposed)) {
			$exposed = $this->form()->fields()->keys();
		}

		// get the values & keys of the exposed fields
		$content = '';
		foreach ($exposed as $fieldId) {
			$field = $this->form()->fields()->find($fieldId);
			$value = $this->submission()->valueForId($fieldId);

			if ($field && $value?->isNotEmpty()) {
				// add the field key and the value to the webhook content
				$content .= "**{$field->label()}**\n{$value}\n\n";
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
			$request = Remote::post($this->webhookUrl(), [
				'headers' => [
					'User-Agent' => DreamForm::userAgent(),
					'Content-Type' => 'application/json'
				],
				'data' => Json::encode([
					'content' => null,
					'embeds' => [
						[
							'title' => $this->form()->title()->value(),
							'description' => $this->content(),
							"author" => [
								"name" => "New submission"
							],
							'footer' => [
								'text' => App::instance()->site()->title()->value(),
								'icon_url' => 'https://www.google.com/s2/favicons?domain=' . App::instance()->site()->url() . '&sz=32'
							],
							'timestamp' => date('c', $this->submission()->sortDate())
						]
					],
					'attachments' => []
				])
			]);
		} catch (Throwable $e) {
			$this->cancel($e->getMessage());
		}

		if ($request->code() > 299) {
			$this->cancel('dreamform.actions.discord.log.error');
		}

		$meta = Remote::get($this->webhookUrl(), [
			'headers' => [
				'User-Agent' => DreamForm::userAgent()
			]
		]);
		$this->log([
			'template' => [
				'name' => $meta->json()['name'],
			]
		], type: 'none', icon: 'discord', title: 'dreamform.actions.discord.log.success');
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
			'icon' => 'discord',
			'title' => 'dreamform.actions.discord.name'
		];
	}
}
