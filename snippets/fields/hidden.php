<?php

/**
 * @var \tobimori\DreamForm\Models\Submission|null $submission
 *
 * @var \Kirby\Cms\Block $block
 * @var \tobimori\DreamForm\Fields\HiddenField $field
 * @var \tobimori\DreamForm\Models\FormPage $form
 * @var array $attr
 */

use Kirby\Toolkit\A;

$attr = A::merge($attr, $attr['hidden']); ?>

<input <?= attr(A::merge($attr['input'], [
	'type' => 'hidden',
	'id' => $form->elementId($block->id()),
	'name' => $block->key(),
	'value' => $form->valueFor($block->key())
])) ?>>
