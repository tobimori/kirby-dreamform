<?php

namespace tobimori\DreamForm\Fields;

class EmailField extends Field
{
	public static $type = 'email';

	public static function blueprint(): array
	{
		return [
			'title' => t('email-field'),
			'preview' => 'fields',
			'wysiwyg' => true,
			'icon' => 'email',
		];
	}
}
