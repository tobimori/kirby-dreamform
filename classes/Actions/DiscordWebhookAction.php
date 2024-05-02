<?php

namespace tobimori\DreamForm\Actions;

use Kirby\Cms\App;
use Kirby\Data\Json;
use Kirby\Http\Remote;
use Throwable;

/**
 * Action for sending a message in a discord channel.
 */
class DiscordWebhookAction extends Action
{
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
							'type' => 'url',
							'placeholder' => 'https://discord.com/api/webhooks/...',
							'width' => '1/3',
							'required' => !App::instance()->option('tobimori.dreamform.actions.discord.webhook')
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

	public function webhookUrl(): string
	{
		return $this->block()->webhookUrl()->or(App::instance()->option('tobimori.dreamform.actions.discord.webhook'));
	}

	public function content(): string
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

	public function run(): void
	{
		try {
			$request = Remote::post($this->webhookUrl(), [
				'headers' => [
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

			if ($request->code() > 299) {
				$this->cancel();
			}
		} catch (Throwable $e) {
			$this->cancel($e->getMessage());
		}
	}

	/**
	 * Returns the actions' blueprint group
	 */
	public static function group(): string
	{
		return 'notifications';
	}
}
