<?php

namespace tobimori\DreamForm\Fields;

class ButtonField extends Field
{
	public static function blueprint(): array
	{
		return [
			'title' => t('button-field'),
			'icon' => 'ticket',
			'preview' => 'fields',
			'wysiwyg' => true,
			'tabs' => [
				'settings' => [
					'label' => t('settings'),
					'fields' => [
						'label' => 'dreamform/fields/label',
					]
				]
			]
		];
	}
}
