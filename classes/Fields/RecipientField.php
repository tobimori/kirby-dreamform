<?php

namespace tobimori\DreamForm\Fields;

class RecipientField extends Field
{
	public static $type = 'recipient';

	public static function blueprint(): array
	{
		return [
			'title' => t('recipient-field'),
			'preview' => 'fields',
			'wysiwyg' => true,
			'icon' => 'email',
		];
	}
}
