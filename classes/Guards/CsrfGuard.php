<?php

namespace tobimori\DreamForm\Guards;

use Kirby\Cms\App;
use tobimori\DreamForm\Models\SubmissionPage;

class CsrfGuard extends Guard
{
	// ensure the same token is used for all forms
	// even if the session storage is disabled
	public static $token = null;

	/**
	 * Returns the CSRF token for the current session
	 */
	public function csrf(): string
	{
		if (self::$token === null) {
			self::$token = App::instance()->csrf();
		}

		return self::$token;
	}

	/**
	 * Validate the CSRF token
	 */
	public function run(): void
	{
		$token = $this->csrf();
		$submitted = SubmissionPage::valueFromBody('dreamform-csrf');

		if ($submitted !== $token) {
			$this->cancel('dreamform.submission.error.csrf', true);
		}
	}

	public static function hasSnippet(): bool
	{
		return true;
	}
}
