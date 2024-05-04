<?php

/**
 * @var \tobimori\DreamForm\Models\Submission|null $submission
 *
 * @var \Kirby\Cms\Block $block
 * @var \tobimori\DreamForm\Fields\TextField $field
 * @var \tobimori\DreamForm\Models\FormPage $form
 * @var array $attr
 *
 * @var string|null $type
 * @var array|null $input
 */

use Kirby\Toolkit\A;

$type ??= 'text';

$attr = A::merge($attr, $attr[$type]);
snippet('dreamform/fields/partials/wrapper', $arguments = compact('block', 'field', 'form', 'attr'), slots: true);
snippet('dreamform/fields/partials/label', $arguments); ?>

<input <?= attr(A::merge($attr['input'], [
	'type' => $type,
	'id' => $form->elementId($block->id()),
	'name' => $block->key(),
	'placeholder' => $type !== 'file' ? $block->placeholder()->or(" ") : null,
	'required' => $block->required()->toBool() ?? null,
	'value' => $type !== 'file' ? $form->valueFor($block->key()) : null
], $input ?? [])) ?>>

<?php snippet('dreamform/fields/partials/error', $arguments);
endsnippet() ?>
