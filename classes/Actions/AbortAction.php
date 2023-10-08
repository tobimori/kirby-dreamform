<?php

namespace tobimori\DreamForm\Actions;

/**
 * Action for aborting the submission process.
 * @package tobimori\DreamForm\Actions
 */
class AbortAction extends Action
{
	public static function blueprint(): array
	{
		return [
			'title' => t('abort-action'),
			'preview' => 'fields',
			'wysiwyg' => true,
			'icon' => 'protected',
			'tabs' => [
				'settings' => [
					'label' => t('settings'),
					'fields' => [
						'errorMessage' => [
							'extends' => 'dreamform/fields/error-message',
							'help' => false
						],
					]
				]
			]
		];
	}

	public function run(): void
	{
	}
}
