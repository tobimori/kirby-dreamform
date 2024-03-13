<?php

namespace tobimori\DreamForm\Fields;

use Kirby\Toolkit\A;
use Kirby\Toolkit\V;
use Kirby\Content\Field as ContentField;
use Kirby\Toolkit\Str;

class CheckboxField extends Field
{
	public static function blueprint(): array
	{
		return [
			'title' => t('dreamform.checkbox-field'),
			'preview' => 'fields',
			'wysiwyg' => true,
			'icon' => 'toggle-off',
			'fields' => [
				'options' => [
					'label' => t('dreamform.options'),
					'type' => 'structure',
					'fields' => [
						'value' => [
							'label' => t('dreamform.value'),
							'help' => t('dreamform.options-value-help'),
							'type' => 'text',
							'width' => '1/2'
						],
						'label' => [
							'label' => t('dreamform.label'),
							'help' => t('dreamform.options-label-help'),
							'type' => 'writer',
							'inline' => true,
							'width' => '1/2',
							'marks' => [
								'bold',
								'italic',
								'link'
							],
						]
					]
				],
				'key' => [
					'extends' => 'dreamform/fields/key',
					'wizard' => false
				],
				'min' => [
					'label' => t('dreamform.min-checked'),
					'type' => 'number',
					'width' => '1/6'
				],
				'max' => [
					'label' => t('dreamform.max-checked'),
					'type' => 'number',
					'width' => '1/6'
				],
				'errorMessage' => [
					'extends' => 'dreamform/fields/error-message',
					'width' => '1/2'
				],
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
			'label' => t('dreamform.checkbox-field') . ': ' . $this->key(),
			'icon' => 'toggle-off',
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
			return $this->block()->errorMessage()->isNotEmpty() ? $this->block()->errorMessage() : t('dreamform.error-message-default');
		}

		return true;
	}

	protected function sanitize(ContentField $value): ContentField
	{
		return new ContentField($this->block()->parent(), $this->key(), A::join($value->value() ?? [], ','));
	}
}
