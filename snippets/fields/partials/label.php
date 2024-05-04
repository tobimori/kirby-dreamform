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

<label <?= attr(A::merge($attr['label'], ["for" => $form->elementId($block->id())])) ?>>
	<span><?= $block->label()->escape() ?></span>
	<?php if ($required = $block->required()->toBool()) : ?>
		<em>*</em>
	<?php endif ?>
</label>
