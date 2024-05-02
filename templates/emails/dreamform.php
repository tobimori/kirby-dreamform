<?= tt('dreamform.actions.email.defaultTemplate.text', null, ['form' => $form->title()]) ?>

----

<?php foreach ($fields = $form->fields()->filterBy(fn ($f) => $f::hasValue()) as $field) : ?>
  <?= $field->label() ?>

  <?= $submission->valueFor($field->key()) ?? "—" ?>

  <?php if ($fields->last() !== $field) : ?>

    ----

<?php endif;
endforeach ?>
