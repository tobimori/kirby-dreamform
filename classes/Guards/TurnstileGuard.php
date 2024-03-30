<?php

namespace tobimori\DreamForm\Guards;

use Kirby\Cms\App;
use Kirby\Http\Remote;
use tobimori\DreamForm\Models\SubmissionPage;

class TurnstileGuard extends Guard
{
	public static function siteKey(): string|null
	{
		return App::instance()->option('tobimori.dreamform.guards.turnstile.siteKey');
	}

	protected static function secretKey(): string|null
	{
		return App::instance()->option('tobimori.dreamform.guards.turnstile.secretKey');
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
			$this->cancel(t('dreamform.captcha-error'));
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
