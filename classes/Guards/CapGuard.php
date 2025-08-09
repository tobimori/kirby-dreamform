<?php

namespace tobimori\DreamForm\Guards;

use Kirby\Http\Remote;
use tobimori\DreamForm\DreamForm;
use tobimori\DreamForm\Models\SubmissionPage;

class CapGuard extends Guard
{
	public const TYPE = 'cap';

	public static function endpoint(): string|null
	{
		return DreamForm::option('guards.cap.endpoint');
	}

	protected static function secretKey(): string|null
	{
		return DreamForm::option('guards.cap.secretKey');
	}

	public function run(): void
	{
		$data = [
			'secret' => static::secretKey(),
			'response' => SubmissionPage::valueFromBody('cap-token')
		];

		$remote = Remote::post(DreamForm::option('guards.cap.endpoint') . 'siteverify', [
			'data' => $data
		]);

		$result = $remote->json();

		if (
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
		return static::endpoint() !== null
			&& static::secretKey() !== null;
	}
}
