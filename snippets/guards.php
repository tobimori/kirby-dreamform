<?php

/**
 * This is the guard form snippet that renders the necessary content for guards.
 *
 * @var \Kirby\Cms\Page $page
 * @var \tobimori\DreamForm\Models\FormPage $form
 * @var array|null $attr
 */

foreach ($form->guards() as $guard) {
	if ($guard::hasSnippet()) {
		snippet(
			"dreamform/guards/{$guard->type()}",
			compact('form', 'attr', 'guard')
		);
	}
}
