<?php

namespace tobimori\DreamForm\Guards;

use tobimori\DreamForm\Models\SubmissionPage;

class TurnstileGuard extends Guard
{
	public function siteKey(): string
	{
		return '';
	}

	public function run(): void
	{
		$this->cancel(t('dreamform.captcha-error'));
	}

	public static function hasSnippet(): bool
	{
		return true;
	}
}
