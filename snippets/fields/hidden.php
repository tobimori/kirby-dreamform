<?php

/**
 * @var \Kirby\Cms\Block $block
 * @var \DreamForm\Models\FormPage $form
 */ ?>

<input <?= attr([
	'type' => 'hidden',
	'id' => $block->id(),
	'name' => $block->key(),
	'value' => $form->valueFor($block->key())->escape()
]) ?>>