<?php

namespace tobimori\DreamForm\Fields;

class ButtonField extends Field
{
	public static function blueprint(): array
	{
		return [
			'title' => t('dreamform.button-field'),
			'icon' => 'ticket',
			'preview' => 'button-field',
			'wysiwyg' => true,
			'tabs' => [
				'settings' => [
					'label' => t('dreamform.settings'),
					'fields' => [
						'label' => [
							'extends' => 'dreamform/fields/label',
							'width' => 1,
							'required' => false,
							'placeholder' => t('dreamform.submit')
						],
					]
				]
			]
		];
	}

	public static function hasValue(): bool
	{
		return false;
	}
}
