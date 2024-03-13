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
			<?= $block->label() ?>
		</span>
		<?php if ($required = $block->required()->toBool()) : ?>
			<em>*</em>
		<?php endif ?></label>
	<select <?= attr([
		'id' => $block->id(),
		'name' => $block->key(),
		'required' => $required ?? null,
	]) ?>>
		<option value="" disabled selected hidden><?= $block->placeholder() ?></option>
		<?php foreach ($block->options()->toStructure() as $option) : ?>
			<option <?= attr(['value' => $option->value()]) ?>>
				<?= $option->label() ?>
			</option>
		<?php endforeach ?>
	</select>
	<span <?= attr(A::merge($error ?? [], [
		'data-error' => $block->key()
	])) ?>>
		<?= $submission?->errorFor($block->key()) ?>
	</span>
</div>