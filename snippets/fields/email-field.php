<?php

/**
 * @var \Kirby\Cms\Block $block
 * @var \DreamForm\Models\FormPage $form
 * @var array|null $input
 * @var array|null $error
 */

snippet('dreamform/fields/text-field', [
	'block' => $block,
	'form' => $form,
	'type' => 'email',
	'input' => $input ?? null,
	'error' => $error ?? null
]);
