<?php

namespace tobimori\DreamForm\Guards;

use Kirby\Http\Remote;
use tobimori\DreamForm\DreamForm;
use tobimori\DreamForm\Models\SubmissionPage;

class HCaptchaGuard extends Guard
{
	public const TYPE = 'hcaptcha';

	public static function siteKey(): string|null
	{
		return DreamForm::option('guards.hcaptcha.siteKey');
	}

	protected static function secretKey(): string|null
	{
		return DreamForm::option('guards.hcaptcha.secretKey');
	}

	public function run(): void
	{
		$data = [
			'secret' => static::secretKey(),
			'response' => SubmissionPage::valueFromBody('h-captcha-response')
		];

		// Only include remoteip if IP metadata collection is enabled
		if (in_array('ip', DreamForm::option('metadata.collect', []))) {
			// we can't access the metadata object yet
			$data['remoteip'] = App::instance()->visitor()->ip();
		}

		$remote = Remote::post('https://api.hcaptcha.com/siteverify', [
			'data' => $data
		]);

		$result = $remote->json();

		if (
			$remote->code() !== 200 ||
			$result['success'] !== true
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
