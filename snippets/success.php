<?php

/**
 * This is the success page snippet for DreamForm.
 *
 * @var \Kirby\Cms\Page $page
 * @var \tobimori\Dreamform\Models\FormPage $form
 * @var array|null $attr
 */

use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;

?>

<div <?= attr(A::merge($attr['success'] ?? [], ['id' => $form->elementId()])) ?>>
	<?= $submission->toString(
		$form->successMessage()->or(t('dreamform.form.successMessage.default'))->value(),
		A::map($submission->values()->toArray(), fn ($str) => $str ? Str::esc($str, 'html') : "")
	) ?>
</div>