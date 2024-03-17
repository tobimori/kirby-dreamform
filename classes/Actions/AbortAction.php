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
							'placeholder' => t('dreamform.generic-error'),
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
		if ($this->block()->showError()->toBool()) {
			$this->cancel($this->block()->errorMessage()->or(t('dreamform.generic-error')), true);
		} else {
			$this->success();
		}
	}
}
