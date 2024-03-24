<?php

/**
 * @var \Kirby\Cms\Block $block
 * @var \tobimori\DreamForm\Fields\HiddenField $field
 * @var \tobimori\DreamForm\Models\FormPage $form
 * @var \tobimori\DreamForm\Models\Submission|null $submission
 */ ?>

<input <?= attr([
	'type' => 'hidden',
	'id' => $block->id(),
	'name' => $block->key(),
	'value' => $form->valueFor($block->key())
]) ?>>