<?php

namespace tobimori\DreamForm\Fields;

use Kirby\Toolkit\V;

class NumberField extends Field
{
	public static function blueprint(): array
	{
		return [
			'name' => t('dreamform.fields.number.name'),
			'preview' => 'text-field',
			'wysiwyg' => true,
			'icon' => 'order-num-asc',
			'tabs' => [
				'field' => [
					'label' => t('dreamform.field'),
					'fields' => [
						'key' => 'dreamform/fields/key',
						'label' => 'dreamform/fields/label',
						'placeholder' => [
							'extends' => 'dreamform/fields/placeholder',
							'width' => '1/4'
						],
						'step' => [
							'label' => t('dreamform.fields.number.step.label'),
							'type' => 'number',
							'default' => 1,
							'required' => true,
							'width' => '1/4',
							'help' => t('dreamform.fields.number.step.help')
						],
					]
				],
				'validation' => [
					'label' => t('dreamform.validation'),
					'fields' => [
						'min' => [
							'label' => t('dreamform.fields.number.min.label'),
							'type' => 'number',
							'width' => '1/3'
						],
						'max' => [
							'label' => t('dreamform.fields.number.max.label'),
							'type' => 'number',
							'width' => '1/3'
						],
						'required' => 'dreamform/fields/required',
						'errorMessage' => [
							'extends' => 'dreamform/fields/error-message',
							'width' => '1'
						],
					]
				]
			]
		];
	}

	public function submissionBlueprint(): array|null
	{
		return [
			'label' => $this->block()->label()->value() ?? t('dreamform.fields.number.name'),
			'type' => 'number'
		];
	}

	public function validate(): true|string
	{
		$value = $this->value()->toFloat();

		if (
			// check for required field
			$this->block()->required()->toBool()
			&& $this->value()->isEmpty()

			// check for max
			|| $this->block()->max()->isNotEmpty()
			&& !V::max($value, $this->block()->max()->toFloat())

			// check for min
			|| $this->block()->min()->isNotEmpty()
			&& !V::min($value, $this->block()->min()->toFloat())

			// check for step
			|| fmod($value, $this->block()->step()->toFloat()) !== 0.0
		) {
			return $this->errorMessage();
		}

		return true;
	}
}
