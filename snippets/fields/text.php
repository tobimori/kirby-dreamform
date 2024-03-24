<?php

/**
 * @var \Kirby\Cms\Block $block
 * @var \tobimori\DreamForm\Fields\TextField $field
 * @var \tobimori\DreamForm\Models\FormPage $form
 * @var \tobimori\DreamForm\Models\Submission|null $submission
 * @var string|null $type
 * @var array|null $attr
 * @var array|null $input
 * @var array|null $error
 */

use Kirby\Toolkit\A;

$type ??= 'test'; ?>

<div <?= attr(A::merge($input ?? [], ['data-has-error' => !!$submission?->errorFor($block->key())])) ?>>
	<label for="<?= $block->id() ?>">
		<span>
			<?= $block->label()->escape() ?>
		</span>
		<?php if ($required = $block->required()->toBool()) : ?>
			<em>*</em>
		<?php endif ?></label>
	<input <?= attr(A::merge([
		'type' => $type,
		'id' => $block->id(),
		'name' => $block->key(),
		'placeholder' => $type !== 'file' ? $block->placeholder()->or(" ") : null,
		'required' => $required ?? null,
		'value' => $type !== 'file' ? $form->valueFor($block->key()) : null
	], $attr ?? [])) ?>>
	<span <?= attr(A::merge($error ?? [], [
		'data-error' => $block->key()
	])) ?>>
		<?= $submission?->errorFor($block->key()) ?>
	</span>
</div>