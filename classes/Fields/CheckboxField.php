<?php

namespace tobimori\DreamForm\Fields;

use Kirby\Toolkit\V;

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
				'errorMessage' => 'dreamform/fields/error-message',
			]
		];
	}

	public function validate(): true|string
	{
		if (
			$this->field()->max()->isNotEmpty()
			&& !V::max(count($this->value()->value() ?? []), $this->field()->max()->toInt())
			|| $this->field()->min()->isNotEmpty()
			&& !V::min(count($this->value()->value() ?? []), $this->field()->min()->toInt())
		) {
			return $this->field()->errorMessage()->isNotEmpty() ? $this->field()->errorMessage() : t('error-message-default');
		}

		return true;
	}
}
