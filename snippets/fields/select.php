<?php

/**
 * @var \tobimori\DreamForm\Models\Submission|null $submission
 *
 * @var \Kirby\Cms\Block $block
 * @var \tobimori\DreamForm\Fields\SelectField $field
 * @var \tobimori\DreamForm\Models\FormPage $form
 * @var array $attr
 */

use Kirby\Toolkit\A;
use Kirby\Toolkit\Escape;

$attr = A::merge($attr, $attr['select']);
snippet('dreamform/fields/partials/wrapper', $arguments = compact('block', 'field', 'form', 'attr'), slots: true);
snippet('dreamform/fields/partials/label', $arguments); ?>

<select <?= attr(A::merge($attr['input'], [
	'id' => $block->id(),
	'name' => $block->key(),
	'required' => $required ?? null,
])) ?>>
	<?php if ($block->placeholder()->isNotEmpty()) : ?>
		<option value="" disabled selected hidden><?= $block->placeholder()->escape() ?></option>
	<?php endif ?>
	<?php foreach ($field->options() as $value => $label) : ?>
		<option <?= attr(['value' => $value]) ?>>
			<?= Escape::html($label) ?>
		</option>
	<?php endforeach ?>
</select>

<?php snippet('dreamform/fields/partials/error', $arguments);
endsnippet() ?>