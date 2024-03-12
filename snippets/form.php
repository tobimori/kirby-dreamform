<?php

/**
 * This is the base form snippet for Dream Form.
 * You can use this snippet in your site or copy it to customize it.
 *
 * @var FormPage $form
 *
 * @var string|null $formClass
 * @var string|null $rowClass
 * @var string|null $columnClass

 * @var string|null $inputClass
 * @var string|null $btnClass
 * @var string|null $errorClass
 */

// don't show the form if it's a draft
// TODO: inactive form snippet
if (!$form || $form->status() === 'draft') {
	return;
}

if ($submission?->isFinished()) {
	snippet('dreamform/success', ['form' => $form]);
	return;
} ?>

<form <?= attr([
	'action' => $form->url(),
	'method' => 'POST',
	'class' => $formClass ?? null,
	'novalidate' => 'novalidate'
]) ?>>
	<div <?= attr(['class' => $errorClass ?? null, 'data-error' => true]) ?>><?= $submission?->error() ?></div>
	<?php foreach ($form->fieldLayouts() as $row) : ?>
		<div <?= attr([
			'style' => 'display: grid; grid-template-columns: repeat(12, 1fr);',
			'class' => $rowClass ?? null,
		]) ?>>
			<?php foreach ($row->columns() as $column) : ?>
				<div <?= attr([
					'style' => "grid-column-start: span {$column->span(12)};",
					'class' => $columnClass ?? null,
				]) ?>>
					<?php foreach ($column->blocks() as $field) {
						snippet(
							"dreamform/fields/{$field->type()}",
							[
								'block' => $field,
								'form' => $form,
								'inputClass' => $inputClass ?? null,
								'btnClass' => $btnClass ?? null
							]
						);
					} ?>
				</div>
			<?php endforeach ?>
		</div>
	<?php endforeach ?>
</form>