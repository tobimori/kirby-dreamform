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

$precognition = Htmx::isActive() && DreamForm::option('precognition');
$targetAttribute = Htmx::isVersion(4) ? 'hx-target:inherited' : 'hx-target';
$syncAttribute = Htmx::isVersion(4) ? 'hx-sync:inherited' : 'hx-sync';

?>

<div <?= attr(A::merge($attr['field'] ?? [], [
	$targetAttribute => $precognition ? 'this' : null,
	$syncAttribute => $precognition ? 'this:replace' : null,
	'data-has-error' => !!$submission?->errorFor($block->key(), $form)
])) ?>>
	<?= $slot ?>
</div>
