<?php

namespace tobimori\DreamForm\Actions;

class RedirectAction extends Action
{
	public static function blueprint(): array
	{
		return [
			'title' => t('redirect-action'),
			'preview' => 'fields',
			'wysiwyg' => true,
			'icon' => 'shuffle',
			'tabs' => []
		];
	}

	public function run(): void
	{
	}
}
