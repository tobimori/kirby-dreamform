<?php

namespace tobimori\DreamForm\Fields;

class CheckboxField extends Field
{
	public static $type = 'checkbox';

	public static function blueprint(): array
	{
		return [
			'title' => t('checkbox-field'),
			'preview' => 'fields',
			'wysiwyg' => true,
			'icon' => 'toggle-off',
		];
	}

	public function validate($value): true|string
	{
		return true;
	}
}
