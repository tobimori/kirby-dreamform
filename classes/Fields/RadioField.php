<?php

namespace tobimori\DreamForm\Fields;

class RadioField extends Field
{
	public static function blueprint(): array
	{
		return [
			'title' => t('dreamform.radio-field'),
			'label' => '{{ label }}',
			'preview' => 'fields',
			'wysiwyg' => true,
			'icon' => 'circle-nested',
			'tabs' => [
				'field' => [
					'label' => t('dreamform.field'),
					'fields' => [
						'key' => 'dreamform/fields/key',
						'label' => [
							'extends' => 'dreamform/fields/label',
							'width' => '5/6',
							'required' => false
						],
						'options' => 'dreamform/fields/options',
					]
				],
				'validation' => [
					'label' => t('dreamform.validation'),
					'fields' => [
						'required' => 'dreamform/fields/required',
						'errorMessage' => 'dreamform/fields/error-message',
					]
				]
			]
		];
	}

	public function submissionBlueprint(): array|null
	{
		$options = [];
		foreach ($this->block()->options()->toStructure() as $option) {
			$options[$option->value()->value()] = $option->label()->value();
		}

		return [
			'label' => t('dreamform.radio-field') . ': ' . $this->key(),
			'type' => 'radio',
			'options' => $options
		];
	}

	public function validate(): true|string
	{
		if ($this->block()->required()->toBool() && $this->value()->isEmpty()) {
			return $this->errorMessage();
		}

		return true;
	}
}
