<?php

namespace tobimori\DreamForm\Guards;

use Kirby\Http\Remote;
use tobimori\DreamForm\DreamForm;
use tobimori\DreamForm\Models\SubmissionPage;

class TurnstileGuard extends Guard
{
	public static function siteKey(): string|null
	{
		return DreamForm::option('guards.turnstile.siteKey');
	}

	protected static function secretKey(): string|null
	{
		return DreamForm::option('guards.turnstile.secretKey');
	}

	public function run(): void
	{
		$remote = Remote::post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
			'data' => [
				'secret' => static::secretKey(),
				'response' => SubmissionPage::valueFromBody('cf-turnstile-response')
			]
		]);

		if (
			$remote->code() !== 200 ||
			$remote->json()['success'] !== true
		) {
			$this->cancel(t('dreamform.submission.error.captcha'));
		}
	}

	public static function hasSnippet(): bool
	{
		return true;
	}

	public static function isAvailable(): bool
	{
		return static::siteKey() !== null
			&& static::secretKey() !== null;
	}
}
