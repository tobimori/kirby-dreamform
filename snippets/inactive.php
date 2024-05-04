<?php

/**
 * This is the inactive form snippet for DreamForm.
 *
 * @var \Kirby\Cms\Page $page
 * @var array|null $attr
 */

use Kirby\Toolkit\A;

?>

<div <?= attr(A::merge($attr['inactive'], ['id' => $form->elementId()])) ?>>
	<?= t('dreamform.form.inactiveMessage.default') ?>
</div>
