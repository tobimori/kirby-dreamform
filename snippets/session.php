<?php

/**
 * This is the base form snippet for DreamForm.
 * You can use this snippet in your site or copy it to customize it.
 *
 * @var \tobimori\DreamForm\Models\FormPage $form
 * @var \tobimori\DreamForm\Models\SubmissionPage|null $submission
 */

use tobimori\DreamForm\Support\Htmx;

if (Htmx::isActive()) : ?>
	<input <?= attr([
		'type' => 'hidden',
		'id' => $id = $form->uuid()->id() . '-session',
		'name' => 'dreamform:session',
		'value' => $submission ? Htmx::encrypt(($submission->exists() ? "page://" : "") . $submission->slug()) : null,
		'hx-swap-oob' => isset($swap) && $swap ? "outerHTML:#{$id}" : null
	]) ?>>
<?php endif ?>
