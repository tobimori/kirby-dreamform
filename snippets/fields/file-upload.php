<?php

/**
 * @var \tobimori\DreamForm\Models\Submission|null $submission
 *
 * @var \Kirby\Cms\Block $block
 * @var \tobimori\DreamForm\Fields\TextField $field
 * @var \tobimori\DreamForm\Models\FormPage $form
 * @var array $attr
 */

snippet('dreamform/fields/text', [
	'block' => $block,
	'form' => $form,
	'field' => $field,
	'attr' => $attr,
	'type' => 'file',
	'input' => [
		'name' => $block->key() . ($block->allowMultiple()->toBool() ? '[]' : ''),
		$block->allowMultiple()->toBool() ? 'multiple' : '',
	]
]);
