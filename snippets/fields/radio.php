<?php

/**
 * @var \Kirby\Cms\Block $block
 * @var \tobimori\DreamForm\Fields\RadioField $field
 * @var \tobimori\DreamForm\Models\FormPage $form
 * @var \tobimori\DreamForm\Models\Submission|null $submission
 * @var array|null $input
 * @var array|null $error
 */

snippet('dreamform/fields/checkbox', [
	'block' => $block,
	'field' => $field,
	'form' => $form,
	'type' => 'radio',
	'input' => $input ?? null,
	'error' => $error ?? null
]);
