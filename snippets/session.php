<?php

/**
 * This is the base form snippet for DreamForm.
 * You can use this snippet in your site or copy it to customize it.
 *
 * @var \tobimori\DreamForm\Models\FormPage $form
 * @var \tobimori\DreamForm\Models\SubmissionPage|null $submission
 */

use Kirby\Uuid\Uuid;
use tobimori\DreamForm\DreamForm;
use tobimori\DreamForm\Support\Htmx;

if (Htmx::isActive()) : ?>
	<input <?= attr([
		'type' => 'hidden',
		'id' => $id = $form->uuid()->id() . '-session',
		'name' => 'dreamform:session',
		'value' => $submission ? Htmx::encrypt(($submission->exists() ? "page://" : "") . $submission->slug()) : null,
		'hx-swap-oob' => isset($swap) && $swap ? "outerHTML:[id='{$id}']" : null
	]) ?>>
	<?php if (DreamForm::option('precognition') && (!isset($swap) || !$swap)) : ?>
		<input <?= attr([
			'type' => 'hidden',
			'id' => $form->uuid()->id() . '-request-instance',
			'name' => Htmx::REQUEST_INSTANCE,
			'value' => Htmx::encrypt(Htmx::requestInstance() ?? Uuid::generate()),
			'hx-preserve' => true
		]) ?>>
		<input <?= attr([
			'type' => 'hidden',
			'id' => $form->uuid()->id() . '-request-sequence',
			'name' => Htmx::REQUEST_SEQUENCE,
			'value' => Htmx::requestSequence() ?? 0,
			'hx-preserve' => true
		]) ?>>
	<?php endif ?>
<?php endif ?>
