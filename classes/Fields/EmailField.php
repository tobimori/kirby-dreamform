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
			'tabs' => [
				'settings' => [
					'label' => t('settings'),
					'fields' => [
						'label' => 'dreamform/fields/label',
						'placeholder' => 'dreamform/fields/placeholder',
						'required' => 'dreamform/fields/required',
						'errorMessage' => 'dreamform/fields/error-message',
					]
				]
			]
		];
	}

	public function validate($value): true|string
	{
		return true;
	}
}
