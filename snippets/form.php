<?php

/**
 * This is the base form snippet for DreamForm.
 * You can use this snippet in your site or copy it to customize it.
 *
 * @var \Kirby\Cms\Page $page
 * @var \tobimori\DreamForm\Models\FormPage $form
 * @var array|null $attr
 */

use Kirby\Toolkit\A;

$attr = A::merge([
	// general attributes
	'form' => [],
	'row' => [],
	'column' => [],
	'field' => [],
	'label' => [],
	'error' => [],
	'input' => [],
	'button' => [],
	'success' => [],
	'inactive' => [],

	// field-specific attributes
	'textarea' => [
		'field' => [],
		'label' => [],
		'error' => [],
		'input' => [],
	],
	'text' => [
		'field' => [],
		'label' => [],
		'error' => [],
		'input' => [],
	],
	'select' => [
		'field' => [],
		'label' => [],
		'error' => [],
		'input' => [],
	],
	'number' => [
		'field' => [],
		'label' => [],
		'error' => [],
		'input' => [],
	],
	'file' => [
		'field' => [],
		'label' => [],
		'error' => [],
		'input' => [],
	],
	'email' => [
		'field' => [],
		'label' => [],
		'error' => [],
		'input' => [],
	],
	'radio' => [
		'field' => [],
		'label' => [],
		'error' => [],
		'input' => [],
		'row' => []
	],
	'checkbox' => [
		'field' => [],
		'label' => [],
		'error' => [],
		'input' => [],
		'row' => []
	],
	'hidden' => [
		'input' => []
	],
], $attr ?? []);

// don't show the form if it's a draft
if (!$form || $form->status() === 'draft') {
	snippet('dreamform/inactive', ['form' => $form, 'attr' => $attr]);
	return;
}

if ($submission?->isFinished() && $submission->form()->is($form)) {
	snippet('dreamform/success', ['form' => $form, 'attr' => $attr]);
	return;
} ?>

<form <?= attr(A::merge(
	$attr['form'],
	$form->htmxAttr($page, $attr, $submission),
	$form->attr()
)) ?>>
	<div <?= attr(A::merge(['data-error' => true], $attr['error'])) ?>><?= $submission?->errorFor(form: $form) ?></div>
	<?php foreach ($form->currentLayouts() as $layoutRow) : ?>
		<div <?= attr(A::merge($attr['row'], [
			'style' => 'display: grid; grid-template-columns: repeat(12, 1fr);',
		])) ?>>
			<?php foreach ($layoutRow->columns() as $layoutColumn) : ?>
				<div <?= attr(A::merge($attr['column'], [
					'style' => "grid-column-start: span {$layoutColumn->span(12)};",
				])) ?>>
					<?php foreach ($layoutColumn->blocks() as $block) {
						// get the field instance to access field methods
						$field = $block->toFormField($form->fields());

						if ($field) {
							snippet(
								"dreamform/fields/{$field->type()}",
								[
									'block' => $block,
									'field' => $field,
									'form' => $form,
									'attr' => $attr
								]
							);
						}
					} ?>
				</div>
			<?php endforeach ?>
		</div>
	<?php endforeach; ?>
</form>
