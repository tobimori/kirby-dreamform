<?php

/**
 * @var tobimori\DreamForm\Models\FormPage $form
 * @var tobimori\DreamForm\Guards\TurnstileGuard $guard
 */ ?>

<div class="cf-turnstile" data-sitekey="<?= $guard->siteKey() ?>"></div>