<?php

/**
 * This is the guard form snippet that renders the necessary content for guards.
 *
 * @var FormPage $form
 */

foreach ($form->guards() as  $guard) {
	if ($guard::hasSnippet()) {
		snippet(
			"dreamform/guards/{$guard->type()}",
			['form' => $form, 'guard' => $guard]
		);
	}
}
