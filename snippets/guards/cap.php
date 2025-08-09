<?php

/**
 * @var tobimori\DreamForm\Models\FormPage $form
 * @var tobimori\DreamForm\Guards\CapGuard $guard
 */

use Kirby\Data\Json;
use tobimori\DreamForm\DreamForm;
use tobimori\DreamForm\Support\Htmx;

// Prepare attributes for the component
$attrs = [
    'id' => 'cap',
    'data-cap-api-endpoint' => $guard::endpoint(),
    'data-cap-i18n-verifying-label' => t('dreamform.guards.cap.i18n.verifying'),
    'data-cap-i18n-initial-state' => t('dreamform.guards.cap.i18n.initial'),
    'data-cap-i18n-solved-label' => t('dreamform.guards.cap.i18n.solved'),
    'data-cap-i18n-error-label' => t('dreamform.guards.cap.i18n.error')
];

// Render the widget
echo '<cap-widget ' . attr($attrs) . '></cap-widget>';

// Inject script
if (DreamForm::option('guards.cap.injectScript')) :
    $scriptSrc = 'https://cdn.jsdelivr.net/npm/@cap.js/widget';
    if (DreamForm::option('guards.cap.useAssetServer')) {
        $endpoint = $guard::endpoint();
        if (is_string($endpoint) && $endpoint !== '') {
            $parts = parse_url($endpoint);
            if ($parts && isset($parts['scheme'], $parts['host'])) {
                $serverUrl = $parts['scheme'] . '://' . $parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : '');
                $scriptSrc = rtrim($serverUrl, '/') . '/assets/widget.js';
            }
        }
    }
?>
    <script src="<?= $scriptSrc ?>"></script>
    
    <?php if (Htmx::isActive()) : ?>
    <script>
        // HTMX handlers for reset only
        if (typeof htmx !== 'undefined') {
            htmx.on("htmx:beforeSwap", () => {
                // Reset the widget if needed
                const el = document.querySelector("cap-widget");
                if (el) {
                    // Remove and recreate the widget for a clean reset
                    const newEl = el.cloneNode(true);
                    el.parentNode.replaceChild(newEl, el);
                }
            });
        }
    </script>
    <?php endif; ?>
<?php endif; ?>