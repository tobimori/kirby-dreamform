<?php

/**
 * @var \Kirby\Cms\Block $block
 * @var \tobimori\DreamForm\Fields\PagesField $field
 * @var \tobimori\DreamForm\Models\FormPage $form
 * @var \tobimori\DreamForm\Models\Submission|null $submission
 * @var array|null $input
 * @var array|null $error
 */

snippet('dreamform/fields/select', [
	'block' => $block,
	'field' => $field,
	'form' => $form,
	'input' => $input ?? null,
	'error' => $error ?? null
]);
