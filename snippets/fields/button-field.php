<?php

/**
 * @var \Kirby\Cms\Block $block
 * @var \DreamForm\Models\FormPage $form
 * @var array|null $button
 */

use Kirby\Toolkit\A;

?>

<button <?= attr(A::merge($button ?? [], [
	'type' => 'submit',
])) ?>>
	<?= $block->label() ?>
</button>