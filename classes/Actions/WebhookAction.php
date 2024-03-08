<?php

namespace tobimori\DreamForm\Actions;

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
				'conditions' => [
					'label' => t('conditions'),
					'fields' => []
				]
			]
		];
	}

	public function run(): void
	{
	}
}
