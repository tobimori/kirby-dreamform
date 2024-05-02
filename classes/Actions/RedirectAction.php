<?php

namespace tobimori\DreamForm\Actions;

/**
 * Action for redirecting the user to a success page after submitting.
 */
class RedirectAction extends Action
{
	public static function blueprint(): array
	{
		return [
			'name' => t('dreamform.actions.redirect.name'),
			'preview' => 'fields',
			'wysiwyg' => true,
			'icon' => 'shuffle',
			'tabs' => [
				'settings' => [
					'label' => t('dreamform.settings'),
					'fields' => [
						'redirectTo' => [
							'label' => 'dreamform.actions.redirect.redirectTo.label',
							'type' => 'link',
							'options' => [
								'url',
								'page',
								'file'
							],
							'required' => true
						]
					]
				]
			]
		];
	}

	public function run(): void
	{
		$redirect = $this->block()->redirectTo()->toUrl();
		if ($redirect) {
			$this->submission()->setRedirect($redirect);
		}
	}
}
