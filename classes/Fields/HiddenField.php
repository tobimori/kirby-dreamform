<?php

namespace tobimori\DreamForm\Fields;

class HiddenField extends Field
{
	public static function blueprint(): array
	{
		return [
			'name' => t('dreamform.fields.hidden.name'),
			'preview' => 'hidden-field',
			'wysiwyg' => true,
			'icon' => 'hidden',
			'tabs' => [
				'settings' => [
					'label' => t('dreamform.settings'),
					'fields' => [
						'key' => [
							'extends' => 'dreamform/fields/key',
							'width' => 1,
							'wizard' => false,
							'placeholder' => t('dreamform.fields.hidden.placeholder')
						],
					]
				]
			]
		];
	}

	public function submissionBlueprint(): array|null
	{
		return [
			'label' => t('dreamform.fields.hidden.name') . ': ' . $this->key(),
			'icon' => 'hidden',
			'type' => 'text'
		];
	}

	public static function group(): string
	{
		return 'advanced-fields';
	}
}
