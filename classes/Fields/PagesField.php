<?php

namespace tobimori\DreamForm\Fields;

use Kirby\Cms\App;

class PagesField extends Field
{
	public static function blueprint(): array
	{
		return [
			'title' => t('dreamform.pages-field'),
			'label' => '{{ label }}',
			'preview' => 'fields',
			'wysiwyg' => true,
			'icon' => 'document',
			'tabs' => [
				'field' => [
					'label' => t('dreamform.field'),
					'fields' => [
						'key' => 'dreamform/fields/key',
						'label' => 'dreamform/fields/label',
						'placeholder' => 'dreamform/fields/placeholder',
						'pages' => [
							'label' => t('dreamform.options'),
							'type' => 'pages',
							'query' => App::instance()->option('tobimori.dreamform.fields.pages.query'),
							'width' => '2/3',
						],
						'useChildren' => [
							'label' => t('dreamform.use-children'),
							'help' => t('dreamform.use-children-help'),
							'type' => 'toggle',
							'default' => false,
							'width' => '1/3',
						],
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
			'label' => $this->block()->label()->value() ?? t('dreamform.pages-field'),
			'type' => 'pages'
		];
	}

	/**
	 * Returns the available options as a key-value array.
	 */
	public function options(): array
	{
		$options = [];
		/** @var \Kirby\Cms\Pages $pages */
		$pages = $this->block()->pages()->toPages();

		if ($this->block()->useChildren()->toBool()) {
			$pages = $pages->children()->listed();
		}

		foreach ($pages as $page) {
			$options[$page->uuid()->toString() ?? $page->id()] = $page->title()->value();
		}

		return $options;
	}

	/**
	 * Validate if the selected page exists and is allowed.
	 */
	public function validate(): true|string
	{
		if (
			!array_key_exists($this->value()->toString(), $this->options()) ||
			$this->block()->required()->toBool()
			&& $this->value()->isEmpty()
		) {
			return $this->errorMessage();
		}

		return true;
	}

	public static function group(): string
	{
		return 'advanced-fields';
	}
}
