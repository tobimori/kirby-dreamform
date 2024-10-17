<?php

/**
 * @var \tobimori\DreamForm\Models\Submission|null $submission
 *
 * @var \Kirby\Cms\Block $block
 * @var \tobimori\DreamForm\Fields\Field $field
 * @var \tobimori\DreamForm\Models\FormPage $form
 * @var array $attr
 */

use Kirby\Toolkit\A;

$error = $submission?->errorFor($block->key(), $form) ?>

<span <?= attr(A::merge(
	$attr['error'] ?? [],
	[
		'data-error' => $block->key(),
		'id' => $form->elementId("{$block->id()}/error"),
		'role' => 'alert',
		'aria-atomic' => true
	]
)) ?>><?= $error ?></span>