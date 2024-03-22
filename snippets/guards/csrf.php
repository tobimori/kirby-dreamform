<?php

/**
 * @var tobimori\DreamForm\Models\FormPage $form
 * @var tobimori\DreamForm\Guards\CsrfGuard $guard
 */ ?>

<input type="hidden" name="dreamform-csrf" value="<?= $guard->csrf() ?>">