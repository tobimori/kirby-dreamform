<?php

/**
 * @var \Kirby\Cms\Block $block
 * @var \tobimori\DreamForm\Fields\TextField $field
 * @var \tobimori\DreamForm\Models\FormPage $form
 * @var \tobimori\DreamForm\Models\Submission|null $submission
 * @var array|null $input
 * @var array|null $error
 */

snippet('dreamform/fields/text', [
	'block' => $block,
	'form' => $form,
	'field' => $field,
	'type' => 'file',

	'input' => $input ?? null,
	'error' => $error ?? null,
	'attr' => []
]);
