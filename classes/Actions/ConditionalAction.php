<?php

namespace tobimori\DreamForm\Actions;

/**
 * Action for conditionally running other actions.
 * @package tobimori\DreamForm\Actions
 */
class ConditionalAction extends Action
{
	public static function blueprint(): array
	{
		return [
			'title' => t('conditional-action'),
			'preview' => 'fields',
			'wysiwyg' => true,
			'icon' => 'split',
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
