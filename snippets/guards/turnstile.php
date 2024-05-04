<?php

/**
 * @var tobimori\DreamForm\Models\FormPage $form
 * @var tobimori\DreamForm\Guards\TurnstileGuard $guard
 */

use tobimori\DreamForm\DreamForm;
use tobimori\DreamForm\Support\Htmx;

if (
	DreamForm::option('guards.turnstile.injectScript')
	&& (!Htmx::isActive() || !Htmx::isHtmxRequest())
) : ?>
	<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" defer></script>

	<?php if (Htmx::isActive()) : ?>
		<script>
			htmx.on("htmx:afterSettle", () => {
				const el = document.querySelector(".cf-turnstile");
				if (el) turnstile.render(el)
			});
			htmx.on("htmx:beforeSwap", () => turnstile.remove());
		</script>
<?php endif;
endif ?>

<div <?= attr([
	'class' => 'cf-turnstile',
	'data-theme' => DreamForm::option('guards.turnstile.theme', 'auto'),
	'data-sitekey' => $guard::siteKey()
]) ?>>
</div>