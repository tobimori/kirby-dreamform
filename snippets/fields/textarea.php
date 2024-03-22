<?php

/**
 * @var \Kirby\Cms\Block $block
 * @var \DreamForm\Models\FormPage $form
 * @var array|null $input
 * @var array|null $error
 */

use Kirby\Toolkit\A;

?>

<div <?= attr(A::merge($input ?? [], ['data-has-error' => !!$form->errorFor($block->key())])) ?>>
	<label for="<?= $block->id() ?>">
		<span>
			<?= $block->label() ?>
		</span>
		<?php if ($required = $block->required()->toBool()) : ?>
			<em>*</em>
		<?php endif ?></label>
	<textarea <?= attr([
		'id' => $block->id(),
		'name' => $block->key(),
		'placeholder' => $block->placeholder()->or(" "),
		'required' => $required ?? null,
	]) ?>><?= $form->valueFor($block->key()) ?></textarea>
	<span <?= attr(A::merge($error ?? [], [
		'data-error' => $block->key()
	])) ?>><?= $form->errorFor($block->key()) ?></span>
</div>