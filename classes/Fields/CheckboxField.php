<?php

namespace tobimori\DreamForm\Fields;

use Kirby\Toolkit\V;

class CheckboxField extends Field
{
	public static function blueprint(): array
	{
		return [
			'title' => t('checkbox-field'),
			'preview' => 'fields',
			'wysiwyg' => true,
			'icon' => 'toggle-off',
			'fields' => [
				'options' => [
					'label' => t('options'),
					'type' => 'structure',
					'fields' => [
						'value' => [
							'label' => t('value'),
							'help' => t('options-value-help'),
							'type' => 'text',
							'width' => '1/2'
						],
						'label' => [
							'label' => t('label'),
							'help' => t('options-label-help'),
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
					'label' => t('min-checked'),
					'type' => 'number',
					'width' => '1/6'
				],
				'max' => [
					'label' => t('max-checked'),
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
