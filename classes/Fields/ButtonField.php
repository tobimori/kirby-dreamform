<?php

namespace tobimori\DreamForm\Fields;

class ButtonField extends Field
{
	public static function blueprint(): array
	{
		return [
			'name' => t('dreamform.fields.button.name'),
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
							'placeholder' => t('dreamform.fields.button.label.label')
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
