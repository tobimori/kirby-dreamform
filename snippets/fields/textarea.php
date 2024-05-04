<?php

/**
 * @var \tobimori\DreamForm\Models\Submission|null $submission
 *
 * @var \Kirby\Cms\Block $block
 * @var \tobimori\DreamForm\Fields\TextareaField $field
 * @var \tobimori\DreamForm\Models\FormPage $form
 * @var array $attr
 */

use Kirby\Toolkit\A;

$attr = A::merge($attr, $attr['textarea']);
snippet('dreamform/fields/partials/wrapper', $arguments = compact('block', 'field', 'form', 'attr'), slots: true);
snippet('dreamform/fields/partials/label', $arguments); ?>

<textarea <?= attr(A::merge($attr['input'], [
	'id' => $form->elementId($block->id()),
	'name' => $block->key(),
	'placeholder' => $block->placeholder()->or(" "),
	'required' => $required ?? null,
])) ?>><?= $form->valueFor($block->key())?->escape() ?></textarea>

<?php snippet('dreamform/fields/partials/error', $arguments);
endsnippet() ?>