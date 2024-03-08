<?php

/**
 * @var \Kirby\Cms\Block $block
 * @var \DreamForm\Models\FormPage $form
 * @var string $btnClass
 */ ?>

<button <?= attr([
					'class' => $btnClass ?? '',
					'type' => 'submit'
				]) ?>>
	<?= $block->label() ?>
</button>