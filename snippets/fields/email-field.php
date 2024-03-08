<?php

/**
 * @var \Kirby\Cms\Block $block
 * @var \DreamForm\Models\FormPage $form
 * @var string $inputClass
 * @var string $errorClass
 */

snippet('dreamform/fields/text-field', [
	'block' => $block,
	'form' => $form,
	'type' => 'email',
	'inputClass' => $inputClass ?? null,
	'errorClass' => $errorClass ?? null
]);
