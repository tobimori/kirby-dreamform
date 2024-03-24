<?php

/**
 * @var \Kirby\Cms\Block $block
 * @var \tobimori\DreamForm\Fields\CheckboxField $field
 * @var \tobimori\DreamForm\Models\FormPage $form
 * @var \tobimori\DreamForm\Models\Submission|null $submission
 * @var string|null $type
 * @var array|null $input
 * @var array|null $error
 */

use Kirby\Toolkit\A;

$type ??= 'checkbox';

$previousValue = $form->valueFor($block->key())?->value() ?? [];
if (!is_array($previousValue)) {
	$previousValue = [$previousValue];
} ?>

<div <?= attr(A::merge($input ?? [], ['data-has-error' => !!$submission?->errorFor($block->key())])) ?>>
	<div>
		<?php foreach ($block->options()->toStructure() as $option) : ?>
			<div>
				<input <?= attr([
					'type' => $type,
					'id' => $block->id() . '-' . $option->indexOf(),
					'name' => $block->key() . ($type === 'checkbox' ? '[]' : null),
					'value' => $option->value(),
					'checked' => A::has($previousValue, $option->value())
				]) ?>>
				<label for="<?= $block->id() ?>-<?= $option->indexOf() ?>"><?= $option->label()->or($option->value())->escape() ?></label>
			</div>
		<?php endforeach ?>
	</div>
	<span <?= attr(A::merge($error ?? [], [
		'data-error' => $block->key()
	])) ?>>
		<?= $submission?->errorFor($block->key()) ?>
	</span>
</div>