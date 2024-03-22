<?php

/**
 * @var \Kirby\Cms\Block $block
 * @var \DreamForm\Models\FormPage $form
 * @var array|null $input
 * @var array|null $error
 */

use Kirby\Toolkit\A;

?>

<div <?= attr(A::merge($input ?? [], ['data-has-error' => !!$submission?->errorFor($block->key())])) ?>>
	<label for="<?= $block->id() ?>">
		<span>
			<?= $block->label()->escape() ?>
		</span>
		<?php if ($required = $block->required()->toBool()) : ?>
			<em>*</em>
		<?php endif ?></label>
	<input <?= attr([
		'type' => $type ?? 'text',
		'id' => $block->id(),
		'name' => $block->key(),
		'placeholder' => $block->placeholder()->or(" "),
		'required' => $required ?? null,
		'value' => $form->valueFor($block->key())
	]) ?>>
	<span <?= attr(A::merge($error ?? [], [
		'data-error' => $block->key()
	])) ?>>
		<?= $submission?->errorFor($block->key()) ?>
	</span>
</div>