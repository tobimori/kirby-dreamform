<?php

namespace tobimori\DreamForm\Actions;

/**
 * Action for redirecting the user to a success page after submitting.
 *
 * @package tobimori\DreamForm\Actions
 */
class RedirectAction extends Action
{
	public static function blueprint(): array
	{
		return [
			'title' => t('dreamform.redirect-action'),
			'preview' => 'fields',
			'wysiwyg' => true,
			'icon' => 'shuffle',
			'tabs' => [
				'settings' => [
					'label' => t('dreamform.settings'),
					'fields' => [
						'redirectTo' => [
							'label' => 'dreamform.redirect-to',
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

	public function run()
	{
		$redirect = $this->block()->redirectTo()->toUrl();

		return [
			'redirect' => $redirect
		];
	}
}
