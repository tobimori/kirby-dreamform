<?php

/**
 * @var \tobimori\DreamForm\Models\Submission|null $submission
 *
 * @var \Kirby\Cms\Block $block
 * @var \tobimori\DreamForm\Fields\TextField $field
 * @var \tobimori\DreamForm\Models\FormPage $form
 * @var array $attr
 */

use Kirby\Toolkit\A;

?>

<?php snippet('dreamform/fields/partials/wrapper', compact('block', 'field', 'form', 'attr'), slots: true) ?>

<button <?= attr(A::merge($attr['button'], ['type' => 'submit'])) ?>>
	<?= $block->label()->escape() ?>
</button>

<?php endsnippet() ?>