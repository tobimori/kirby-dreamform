<?php

/**
 * This is the success page snippet for DreamForm.
 *
 * @var \Kirby\Cms\Page $page
 * @var \tobimori\Dreamform\Models\FormPage $form
 * @var array|null $attr
 */

use Kirby\Toolkit\A;

?>

<div <?= attr(A::merge($attr['success'], ['id' => $form->elementId()])) ?>>
	<?= $form->successMessage()->or(t('dreamform.form.successMessage.default')) ?>
</div>
