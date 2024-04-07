<?php

namespace tobimori\DreamForm\Guards;

use Kirby\Cms\App;
use Kirby\Data\Json;
use Kirby\Http\Remote;
use Kirby\Toolkit\A;
use tobimori\DreamForm\DreamForm;
use tobimori\DreamForm\Models\SubmissionPage;

class AkismetGuard extends Guard
{
	/**
	 * Run the Akismet validation
	 */
	public function run(): void
	{
	}

	/**
	 * Reports the submission as spam to Akismet
	 */
	public function reportSubmissionAsSpam(SubmissionPage $submission): void
	{
	}

	/**
	 * Reports the submission as ham to Akismet
	 */
	public function reportSubmissionAsHam(SubmissionPage $submission): void
	{
	}

	/**
	 * Returns the Akismet API key
	 */
	protected static function apiKey(): string|null
	{
		$option = App::instance()->option('tobimori.dreamform.guards.akismet.apiKey');
		if (is_callable($option)) {
			return $option();
		}

		return $option;
	}

	/**
	 * Returns the Akismet API URL
	 */
	protected static function apiUrl(): string
	{
		return 'https://rest.akismet.com/1.1';
	}

	/**
	 * Make a request to the Akismet API
	 */
	protected static function post(string $url, array $data = []): Remote
	{
		return Remote::post(
			static::apiUrl() . $url,
			A::merge([
				'headers' => [
					'User-Agent' => DreamForm::userAgent(),
					'Content-Type' => 'application/json'
				],
				'data' => Json::encode(
					A::merge([
						'api_key' => static::apiKey(),
						'blog' => App::instance()->url(),
						'comment_type' => 'contact-form',
					], $data)
				),
			])
		);
	}
	/**
	 * Mark guard as available if an API key is set
	 */
	public static function isAvailable(): bool
	{
		// TODO: check validity of the API key
		return static::apiKey() !== null;
	}
}
