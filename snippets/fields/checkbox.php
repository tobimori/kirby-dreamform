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
	<div>
		<?php foreach ($block->options()->toStructure() as $option) : ?>
			<div>
				<input <?= attr([
					'type' => 'checkbox',
					'id' => $block->id() . '-' . $option->indexOf(),
					'name' => $block->key() . '[]',
					'value' => $option->value(),
					'checked' => A::has($form->valueFor($block->key())->value() ?? [], $option->value())
				]) ?>>
				<label for="<?= $block->id() ?>-<?= $option->indexOf() ?>"><?= $option->label()->escape() ?></label>
			</div>
		<?php endforeach ?>
	</div>
	<span <?= attr(A::merge($error ?? [], [
		'data-error' => $block->key()
	])) ?>>
		<?= $submission?->errorFor($block->key()) ?>
	</span>
</div>