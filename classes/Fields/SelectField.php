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
				'settings' => [
					'label' => t('dreamform.settings'),
					'fields' => [
						'key' => 'dreamform/fields/key',
						'label' => 'dreamform/fields/label',
						'placeholder' => 'dreamform/fields/placeholder',
						'required' => 'dreamform/fields/required',
						'errorMessage' => 'dreamform/fields/error-message',
						'options' => [
							'label' => t('dreamform.options'),
							'type' => 'structure',
							'fields' => [
								'value' => [
									'type' => 'text',
									'label' => t('dreamform.value'),
									'help' => t('dreamform.options-value-help'),
									'width' => '1/2'
								],
								'label' => [
									'extends' => 'dreamform/fields/label',
									'help' => t('dreamform.options-label-help')
								]
							]
						]
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
			'label' => $this->block()->label()->value() ?? t('dreamform.text-field'),
			'type' => 'select',
			'placeholder' => $this->block()->placeholder()->value() ?? '',
			'options' => $options
		];
	}

	public function validate(): true|string
	{
		if (
			$this->block()->required()->toBool()
			&& $this->value()->isEmpty()
		) {
			return $this->block()->errorMessage()->isNotEmpty() ? $this->block()->errorMessage() : t('dreamform.error-message-default');
		}

		return true;
	}
}
