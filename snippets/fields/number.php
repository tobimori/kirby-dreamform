<?php

/**
 * @var \tobimori\DreamForm\Models\Submission|null $submission
 *
 * @var \Kirby\Cms\Block $block
 * @var \tobimori\DreamForm\Fields\NumberField $field
 * @var \tobimori\DreamForm\Models\FormPage $form
 * @var array $attr
 */

snippet('dreamform/fields/text', [
	'block' => $block,
	'field' => $field,
	'form' => $form,
	'attr' => $attr,
	'type' => 'number',
	'input' => [
		'step' => $block->step()->or(1)?->value(),
		'min' => $block->min()->or(null)?->value(),
		'max' => $block->max()->or(null)?->value(),
	]
]);
