<?php

namespace tobimori\DreamForm\Fields;

class TextField extends Field
{
	public static function blueprint(): array
	{
		return [
			'title' => t('text-field'),
			'preview' => 'fields',
			'wysiwyg' => true,
			'icon' => 'title',
			'tabs' => [
				'settings' => [
					'label' => t('settings'),
					'fields' => [
						'label' => 'dreamform/fields/label',
						'placeholder' => 'dreamform/fields/placeholder',
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
