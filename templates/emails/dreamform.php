<?php if (isset($body) && $body !== null): ?>
<?= $body['text'] ?>
<?php else: ?>
<?= tt('dreamform.actions.email.defaultTemplate.text', null, ['form' => $form->title()]) ?>

———

<?php foreach ($fields = $form->formFields()->filterBy(fn ($f) => $f::hasValue() && $f::type() !== 'file-upload') as $field) :
	$value = $submission->valueFor($field->key())?->escape();
	if (str_starts_with($value ?? "", 'page://')) {
		$page = \Kirby\Cms\App::instance()->site()->find($value);
		if ($page) {
			$value = $page->title();
		}
	}
	?>
<?= $field->label() ?>:
<?= $value ?? "—" ?>

<?php if ($fields->last() !== $field) : ?>

———

<?php endif;
endforeach;
endif; ?>
