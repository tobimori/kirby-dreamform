<?php

/**
 * This is the success page snippet for DreamForm.
 *
 * @var \Kirby\Cms\Page $page
 * @var \tobimori\Dreamform\Models\FormPage $form
 * @var array|null $attr
 */ ?>

<div <?= attr($attr['success']) ?>>
	<?= $form->successMessage()->or(t('dreamform.form.successMessage.default')) ?>
</div>