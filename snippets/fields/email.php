<?php

/**
 * @var \tobimori\DreamForm\Models\Submission|null $submission
 *
 * @var \Kirby\Cms\Block $block
 * @var \tobimori\DreamForm\Fields\TextField $field
 * @var \tobimori\DreamForm\Models\FormPage $form
 * @var array $attr
 */

use Kirby\Toolkit\A;

snippet('dreamform/fields/text', [
	'block' => $block,
	'form' => $form,
	'field' => $field,
	'attr' => A::merge($attr, $attr['input']),
	'type' => 'email'
]);
