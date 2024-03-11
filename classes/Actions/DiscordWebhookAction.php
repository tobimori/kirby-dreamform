<?php

namespace tobimori\DreamForm\Actions;

/**
 * Action for sending a message in a discord channel.
 *
 * @package tobimori\DreamForm\Actions
 */
class DiscordWebhookAction extends Action
{
	public static function blueprint(): array
	{
		return [
			'title' => t('dreamform.discord-webhook-action'),
			'preview' => 'fields',
			'wysiwyg' => true,
			'icon' => 'discord',
			'tabs' => [
				'settings' => [
					'label' => t('settings'),
					'fields' => [
						'webhookUrl' => [
							'label' => 'dreamform.webhook-url',
							'type' => 'url',
							'placeholder' => 'https://discord.com/api/webhooks/...',
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
	}
}
