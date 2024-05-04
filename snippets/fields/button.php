<?php

/**
 * @var \tobimori\DreamForm\Models\Submission|null $submission
 *
 * @var \Kirby\Cms\Block $block
 * @var \tobimori\DreamForm\Fields\ButtonField $field
 * @var \tobimori\DreamForm\Models\FormPage $form
 * @var array $attr
 */

use Kirby\Toolkit\A;
use tobimori\DreamForm\Support\Htmx;

if (
	// Output guards before the last button field of the current step
	// so that the context is right for captcha guards
	($buttonFields = $form->fields(
		$submission?->form()->is($form) ? $submission?->currentStep() ?? 1 : 1
	)->filterBy('type', 'button'))
	&& $buttonFields->last() === $field
) {
	snippet('dreamform/guards', compact('form', 'attr'));
}

snippet('dreamform/fields/partials/wrapper', compact('block', 'field', 'form', 'attr'), slots: true) ?>

<button <?= attr(A::merge($attr['button'], [
	'type' => 'submit',
	'hx-disabled-elt' => Htmx::isActive() ? 'this' : null
])) ?>>
	<?= $block->label()->or(t('dreamform.fields.button.label.label'))->escape() ?>
</button>

<?php endsnippet() ?>
