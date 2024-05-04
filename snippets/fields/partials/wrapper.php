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

?>

<div <?= attr(A::merge($attr['field'], ['data-has-error' => !!$submission?->errorFor($block->key(), $form)])) ?>>
	<?= $slot ?>
</div>
