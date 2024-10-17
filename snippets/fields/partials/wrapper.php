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
use tobimori\DreamForm\DreamForm;
use tobimori\DreamForm\Support\Htmx;

?>

<div <?= attr(A::merge($attr['field'] ?? [], [
	'hx-target' => Htmx::isActive() && DreamForm::option('precognition') ? 'this' : null,
	'data-has-error' => !!$submission?->errorFor($block->key(), $form)
])) ?>>
	<?= $slot ?>
</div>