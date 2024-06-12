<?php

namespace tobimori\DreamForm\Fields;

use Kirby\Toolkit\A;
use Kirby\Toolkit\V;
use Kirby\Content\Field as ContentField;

class CheckboxField extends Field
{
	public static function blueprint(): array
	{
		return [
			'name' => t('dreamform.fields.checkboxes.name'),
			'preview' => 'choices-field',
			'wysiwyg' => true,
			'icon' => 'toggle-off',
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
						'min' => [
							'label' => t('dreamform.fields.checkboxes.min.label'),
							'type' => 'number',
							'width' => '1/6'
						],
						'max' => [
							'label' => t('dreamform.fields.checkboxes.max.label'),
							'type' => 'number',
							'width' => '1/6'
						],
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
			$options[] = [
				'value' => $option->value()->value(),
				'text' => $option->label()->or($option->value())->value()
			];
		}

		return [
			'label' => t('dreamform.fields.checkboxes.name') . ': ' . $this->key(),
			'type' => 'checkboxes',
			'options' => $options
		];
	}

	public function validate(): true|string
	{
		$value = $this->value()->split() ?? [];

		if (
			$this->block()->max()->isNotEmpty()
			&& !V::max(count($value), $this->block()->max()->toInt())
			|| $this->block()->min()->isNotEmpty()
			&& !V::min(count($value), $this->block()->min()->toInt())
		) {
			return $this->errorMessage();
		}

		return true;
	}

	protected function sanitize(ContentField $value): ContentField
	{
		return new ContentField($this->block()->parent(), $this->key(), A::join($value->value() ?? [], ','));
	}
}
