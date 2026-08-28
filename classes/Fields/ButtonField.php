<?php

namespace tobimori\DreamForm\Fields;

class ButtonField extends Field
{
	public const TYPE = 'button';

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
							'width' => '1/2',
							'required' => false,
							'placeholder' => t('dreamform.fields.button.label.label')
						],
						'action' => [
							'label' => t('dreamform.fields.button.action.label'),
							'type' => 'select',
							'width' => '1/2',
							'default' => 'submit',
							'options' => [
								'submit' => t('dreamform.fields.button.action.submit'),
								'previous' => t('dreamform.fields.button.action.previous'),
							]
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
