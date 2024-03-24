<?php

/**
 * @var \Kirby\Cms\Block $block
 * @var \tobimori\DreamForm\Fields\SelectField $field
 * @var \tobimori\DreamForm\Models\FormPage $form
 * @var \tobimori\DreamForm\Models\Submission|null $submission
 * @var array|null $input
 * @var array|null $error
 */

use Kirby\Toolkit\A;
use Kirby\Toolkit\Escape;

?>

<div <?= attr(A::merge($input ?? [], ['data-has-error' => !!$submission?->errorFor($block->key())])) ?>>
	<label for="<?= $block->id() ?>">
		<span>
			<?= $block->label()->escape() ?>
		</span>
		<?php if ($required = $block->required()->toBool()) : ?>
			<em>*</em>
		<?php endif ?></label>
	<select <?= attr([
		'id' => $block->id(),
		'name' => $block->key(),
		'required' => $required ?? null,
	]) ?>>
		<option value="" disabled selected hidden><?= $block->placeholder()->escape() ?></option>
		<?php foreach ($field->options() as $value => $label) : ?>
			<option <?= attr(['value' => $value]) ?>>
				<?= Escape::html($label) ?>
			</option>
		<?php endforeach ?>
	</select>
	<span <?= attr(A::merge($error ?? [], [
		'data-error' => $block->key()
	])) ?>>
		<?= $submission?->errorFor($block->key()) ?>
	</span>
</div>