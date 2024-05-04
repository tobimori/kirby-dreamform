<?php

namespace tobimori\DreamForm\Actions;

/**
 * Action for aborting the submission process.
 */
class AbortAction extends Action
{
	/**
	 * Returns the Blocks fieldset blueprint for the actions' settings
	 */
	public static function blueprint(): array
	{
		return [
			'name' => t('dreamform.actions.abort.name'),
			'preview' => 'fields',
			'wysiwyg' => true,
			'icon' => 'protected',
			'tabs' => [
				'settings' => [
					'label' => t('dreamform.settings'),
					'fields' => [
						'showError' => [
							'label' => t('dreamform.actions.abort.showError.label'),
							'type' => 'toggle',
							'default' => true,
							'width' => '1/3',
						],
						'errorMessage' => [
							'extends' => 'dreamform/fields/error-message',
							'help' => false,
							'placeholder' => t('dreamform.submission.error.generic'),
							'when' => [
								'showError' => true
							]
						],
					]
				]
			]
		];
	}

	/**
	 * Run the action
	 */
	public function run(): void
	{
		if ($this->block()->showError()->toBool()) {
			$this->cancel($this->block()->errorMessage()->or(t('dreamform.submission.error.generic')), public: true, log: false);
		} else {
			$this->success();
		}
	}
}
