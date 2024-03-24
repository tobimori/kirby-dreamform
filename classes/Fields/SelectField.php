<?php

namespace tobimori\DreamForm\Fields;

class SelectField extends Field
{
	public static function blueprint(): array
	{
		return [
			'title' => t('dreamform.select-field'),
			'label' => '{{ label }}',
			'preview' => 'fields',
			'wysiwyg' => true,
			'icon' => 'list-bullet',
			'tabs' => [
				'field' => [
					'label' => t('dreamform.field'),
					'fields' => [
						'key' => 'dreamform/fields/key',
						'label' => 'dreamform/fields/label',
						'placeholder' => 'dreamform/fields/placeholder',
						'options' => [
							'extends' => 'dreamform/fields/options',
							'width' => '1'
						]
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

	/**
	 * Returns the available options as a key-value array.
	 */
	public function options(): array
	{
		$options = [];
		foreach ($this->block()->options()->toStructure() as $option) {
			$options[$option->value()->value()] = $option->label()->or($option->value())->value();
		}

		return $options;
	}

	public function submissionBlueprint(): array|null
	{
		return [
			'label' => $this->block()->label()->value() ?? t('dreamform.select-field'),
			'type' => 'select',
			'placeholder' => $this->block()->placeholder()->value() ?? '',
			'options' => $this->options()
		];
	}

	public function validate(): true|string
	{
		if (
			$this->block()->required()->toBool()
			&& $this->value()->isEmpty()
		) {
			return $this->errorMessage();
		}

		return true;
	}
}
