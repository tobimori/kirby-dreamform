<?php

/**
 * @var \Kirby\Cms\Block $block
 * @var \tobimori\DreamForm\Fields\ButtonField $field
 * @var \tobimori\DreamForm\Models\FormPage $form
 * @var \tobimori\DreamForm\Models\Submission|null $submission
 * @var array|null $button
 */

use Kirby\Toolkit\A;

?>

<button <?= attr(A::merge($button ?? [], [
	'type' => 'submit',
])) ?>>
	<?= $block->label()->escape() ?>
</button>