<?php

namespace tobimori\DreamForm\Fields;

class SelectField extends Field
{
	public static function blueprint(): array
	{
		return [
			'title' => t('select-field'),
			'preview' => 'fields',
			'wysiwyg' => true,
			'icon' => 'list-bullet',
			'tabs' => [
				'settings' => [
					'label' => t('settings'),
					'fields' => [
						'key' => 'dreamform/fields/key',
						'label' => 'dreamform/fields/label',
						'placeholder' => 'dreamform/fields/placeholder',
						'required' => 'dreamform/fields/required',
						'errorMessage' => 'dreamform/fields/error-message',
						'options' => [
							'label' => t('options'),
							'type' => 'structure',
							'fields' => [
								'value' => [
									'type' => 'text',
									'label' => t('value'),
									'help' => t('options-value-help'),
									'width' => '1/2'
								],
								'label' => [
									'extends' => 'dreamform/fields/label',
									'help' => t('options-label-help')
								]
							]
						]
					]
				]
			]
		];
	}

	public function validate(): true|string
	{
		if (
			$this->field()->required()->toBool()
			&& $this->value()->isEmpty()
		) {
			return $this->field()->errorMessage()->isNotEmpty() ? $this->field()->errorMessage() : t('error-message-default');
		}

		return true;
	}
}
