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
			'title' => t('dreamform.abort-action'),
			'preview' => 'fields',
			'wysiwyg' => true,
			'icon' => 'protected',
			'tabs' => [
				'settings' => [
					'label' => t('dreamform.settings'),
					'fields' => [
						'showError' => [
							'label' => t('dreamform.show-error'),
							'type' => 'toggle',
							'default' => true,
							'width' => '1/3',
						],
						'errorMessage' => [
							'extends' => 'dreamform/fields/error-message',
							'help' => false,
							'when' => [
								'showError' => true
							]
						],
					]
				]
			]
		];
	}

	public function run(): void
	{
		if ($this->action()->showError()->toBool()) {
			$this->error($this->action()->errorMessage()->or(t('dreamform.error-message-default')), true);
		} else {
			$this->success();
		}
	}
}
