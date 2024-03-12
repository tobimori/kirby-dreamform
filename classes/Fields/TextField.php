<?php

namespace tobimori\DreamForm\Fields;

class TextField extends Field
{
	public static function blueprint(): array
	{
		return [
			'title' => t('dreamform.text-field'),
			'preview' => 'fields',
			'wysiwyg' => true,
			'icon' => 'title',
			'tabs' => [
				'field' => [
					'label' => t('dreamform.field'),
					'fields' => [
						'key' => 'dreamform/fields/key',
						'label' => 'dreamform/fields/label',
						'placeholder' => 'dreamform/fields/placeholder',
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
		return [
			'label' => $this->block()->label()->value() ?? t('dreamform.text-field'),
			'icon' => 'text-left',
			'type' => 'text'
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
