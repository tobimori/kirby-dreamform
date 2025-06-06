<?php

/**
 * @var tobimori\DreamForm\Models\FormPage $form
 * @var tobimori\DreamForm\Guards\HCaptchaGuard $guard
 */

use Kirby\Data\Json;
use tobimori\DreamForm\DreamForm;
use tobimori\DreamForm\Support\Htmx;

$theme = DreamForm::option('guards.hcaptcha.theme', 'auto');
$isCustomTheme = $theme === 'custom' || is_array($theme);
$customEnabled = $isCustomTheme ? 'custom=true' : '';

if (
	DreamForm::option('guards.hcaptcha.injectScript')
	&& (!Htmx::isActive() || !Htmx::isHtmxRequest())
) : ?>
	<script src="https://js.hcaptcha.com/1/api.js?<?= $customEnabled ?>" defer></script>

	<?php if (Htmx::isActive()) : ?>
		<script>
			htmx.on("htmx:afterSettle", () => {
				const el = document.querySelector(".h-captcha");
				if (el && typeof hcaptcha !== 'undefined') {
					<?php if ($isCustomTheme) : ?>
					const theme = <?= Json::encode($theme === 'custom' ? DreamForm::option('guards.hcaptcha.customTheme', []) : $theme) ?>;
					hcaptcha.render(el, {
						sitekey: '<?= $guard::siteKey() ?>',
						theme: theme
					});
					<?php else : ?>
					hcaptcha.render(el);
					<?php endif; ?>
				}
			});
			htmx.on("htmx:beforeSwap", () => {
				if (typeof hcaptcha !== 'undefined') hcaptcha.reset();
			});
		</script>
<?php endif;
endif;

// Prepare attributes for the div
$attrs = [
	'class' => 'h-captcha',
	'data-sitekey' => $guard::siteKey(),
	'data-size' => DreamForm::option('guards.hcaptcha.size', 'normal')
];

// Only add data-theme for non-custom themes
if (!$isCustomTheme) {
	$attrs['data-theme'] = $theme;
} ?>

<div <?= attr($attrs) ?>>
</div>

<?php if ($isCustomTheme && !Htmx::isActive()) : ?>
<script>
	document.addEventListener('DOMContentLoaded', function() {
		const theme = <?= Json::encode($theme === 'custom' ? DreamForm::option('guards.hcaptcha.customTheme', []) : $theme) ?>;
		const el = document.querySelector('.h-captcha');
		if (el && typeof hcaptcha !== 'undefined') {
			hcaptcha.render(el, {
				sitekey: '<?= $guard::siteKey() ?>',
				theme: theme
			});
		}
	});
</script>
<?php endif; ?>