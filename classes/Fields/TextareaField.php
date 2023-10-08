<?php

namespace tobimori\DreamForm\Fields;

class TextareaField extends Field
{
	public static function blueprint(): array
	{
		return [
			'title' => t('textarea-field'),
			'preview' => 'fields',
			'wysiwyg' => true,
			'icon' => 'text-left',
			'tabs' => [
				'field' => [
					'label' => t('field'),
					'fields' => [
						'key' => 'dreamform/fields/key',
						'label' => 'dreamform/fields/label',
						'placeholder' => 'dreamform/fields/placeholder',
					]
				],
				'validation' => [
					'label' => t('validation'),
					'fields' => [
						'required' => 'dreamform/fields/required',
						'errorMessage' => 'dreamform/fields/error-message',
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
