<?php

/**
 * @var \Kirby\Cms\Block $block
 * @var \tobimori\DreamForm\Fields\NumberField $field
 * @var \tobimori\DreamForm\Models\FormPage $form
 * @var \tobimori\DreamForm\Models\Submission|null $submission
 * @var array|null $input
 * @var array|null $error
 */

snippet('dreamform/fields/text', [
	'block' => $block,
	'field' => $field,
	'form' => $form,
	'type' => 'number',
	'input' => $input ?? null,
	'error' => $error ?? null,
	'attr' => [
		'step' => $block->step()->or(1)?->value(),
		'min' => $block->min()->or(null)?->value(),
		'max' => $block->max()->or(null)?->value(),
	]
]);
