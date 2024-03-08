<?php

/**
 * @var \Kirby\Cms\Block $block
 * @var \DreamForm\Models\FormPage $form
 * @var string $inputClass
 * @var string $errorClass
 */ ?>

<div>
	<?php foreach ($block->options()->toStructure() as $option) : ?>
		<label class="py-4 w-full checkbox" data-form-target="input">
			<input data-action="change->form#resetError" name="<?= $block->id() ?>[]" type="checkbox" placeholder=" " value="<?= $option->value() ?>" />
			<p class="prose"><?= $option->label() ?></p>
		</label>
	<?php endforeach ?>
	<span class="block mb-4 -mt-6 font-bold text-body-xs text-status-danger aria-hidden:hidden" data-form-target="error" data-id="<?= $block->id() ?>" aria-hidden="true"></span>
</div>