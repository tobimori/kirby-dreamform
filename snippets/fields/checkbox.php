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
}

$attr = A::merge($attr, $attr[$type]);
snippet('dreamform/fields/partials/wrapper', $arguments = compact('block', 'field', 'form', 'attr'), slots: true);

if ($block->label()->isNotEmpty()) {
	snippet('dreamform/fields/partials/label', $arguments);
} ?>

<?php foreach ($block->options()->toStructure() as $option) : ?>
	<div <?= attr($attr[$type]['row'] ?? []) ?>>
		<input <?= attr(A::merge($attr['input'], [
			'type' => $type,
			'id' => $form->elementId("{$block->id()}-{$option->indexOf()}"),
			'name' => $block->key() . ($type === 'checkbox' ? '[]' : null),
			'value' => $option->value(),
			'checked' => A::has($previousValue, $option->value())
		])) ?>>
		<label for="<?= $form->elementId("{$block->id()}-{$option->indexOf()}") ?>"><?= $option->label()->or($option->value())->escape() ?></label>
	</div>
<?php endforeach ?>

<?php

snippet('dreamform/fields/partials/error', $arguments);
endsnippet(); ?>
