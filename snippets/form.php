<?php

/**
 * This is the base form snippet for DreamForm.
 * You can use this snippet in your site or copy it to customize it.
 *
 * @var FormPage $form
 *
 * @var array|null $attr
 * @var array|null $row
 * @var array|null $column
 * @var array|null $input
 * @var array|null $button
 * @var array|null $error
 */

use Kirby\Toolkit\A;

// don't show the form if it's a draft
if (!$form || $form->status() === 'draft') {
	snippet('dreamform/inactive', ['form' => $form]);
	return;
}

if ($submission?->isFinished()) {
	snippet('dreamform/success', ['form' => $form]);
	return;
} ?>

<form <?= attr(A::merge($attr ?? [], [
	'enctype' => $form->enctype(),
	'action' => $form->url(),
	'method' => 'POST',
	'novalidate' => 'novalidate'
])) ?>>
	<div <?= attr(A::merge(['data-error' => true], $error ?? [])) ?>><?= $submission?->errorFor() ?></div>
	<?php foreach ($form->currentLayouts() as $layoutRow) : ?>
		<div <?= attr(A::merge($row ?? [], [
			'style' => 'display: grid; grid-template-columns: repeat(12, 1fr);',
		])) ?>>
			<?php foreach ($layoutRow->columns() as $layoutColumn) : ?>
				<div <?= attr(A::merge($column ?? [], [
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
									'input' => $input ?? null,
									'button' => $button ?? null,
									'error' => $error ?? null,
								]
							);
						}
					} ?>
				</div>
			<?php endforeach ?>
		</div>
	<?php endforeach ?>
	<?php snippet('dreamform/guards', ['form' => $form]) ?>
</form>