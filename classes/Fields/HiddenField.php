<?php

namespace tobimori\DreamForm\Fields;

class HiddenField extends Field
{
	public static function blueprint(): array
	{
		return [
			'title' => t('hidden-field'),
			'preview' => 'fields',
			'wysiwyg' => true,
			'icon' => 'hidden',
			'tabs' => [
				'settings' => [
					'label' => t('settings'),
					'fields' => [
						'key' => [
							'extends' => 'dreamform/fields/key',
							'width' => '2/3'
						],
					]
				]
			]
		];
	}

	public static function hasValue()
	{
		return false;
	}
}
