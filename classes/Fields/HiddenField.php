<?php

namespace tobimori\DreamForm\Fields;

class HiddenField extends Field
{
	public static function blueprint(): array
	{
		return [
			'title' => t('dreamform.hidden-field'),
			'preview' => 'fields',
			'wysiwyg' => true,
			'icon' => 'hidden',
			'tabs' => [
				'settings' => [
					'label' => t('dreamform.settings'),
					'fields' => [
						'key' => [
							'extends' => 'dreamform/fields/key',
							'width' => '2/3'
						],
					]
				]
			]
		];
	}

	public function submissionBlueprint(): array|null
	{
		return [
			'label' => t('dreamform.hidden-field') . ': ' . $this->block()->key()->value(),
			'icon' => 'hidden',
			'type' => 'text'
		];
	}
}
