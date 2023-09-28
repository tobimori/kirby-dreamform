<?php

namespace tobimori\DreamForm\Fields;

class TextField extends Field
{
	public static $type = 'text';

	public static function blueprint(): array
	{
		return [
			'title' => t('text-field'),
			'preview' => 'fields',
			'wysiwyg' => true,
			'icon' => 'title',
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
}
