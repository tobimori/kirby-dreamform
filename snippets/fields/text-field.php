<?php

/**
 * @var \Kirby\Cms\Block $block
 * @var \DreamForm\Models\FormPage $form
 * @var string $inputClass
 * @var string $errorClass
 */ ?>

<div <?= attr(['class' => $inputClass ?? null]) ?>>
	<label for="<?= $block->id() ?>">
		<span>
			<?= $block->label() ?>
		</span>
		<?php if ($required = $block->required()->toBool()) : ?>
			<em>*</em>
		<?php endif ?></label>
	<input <?= attr([
		'type' => $type ?? 'text',
		'id' => $block->id(),
		'name' => $block->key(),
		'placeholder' => $block->placeholder(),
		'required' => $required ?? null,
	]) ?>>
	<span <?= attr([
		'class' => $errorClass ?? null,
		'data-error' => $block->key()
	]) ?>>
		<?= $submission?->errorFor($block->key()) ?>
	</span>
</div>