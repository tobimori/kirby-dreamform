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
	 * Akismet guard doesn't trigger pre-validation checks
	 */
	public function run(): void
	{
	}

	/**
	 * Run the Akismet validation
	 */
	public function postValidation(SubmissionPage $submission): void
	{
		$kirby = App::instance();
		$visitor = $kirby->visitor();
		$request = $kirby->request();

		try {
			$content = [];
			foreach ($kirby->option('tobimori.dreamform.guards.akismet.fields', []) as $key => $fields) {
				$content[$key] = A::reduce($fields, function ($prev, $field) use ($submission) {
					if ($prev !== null) {
						return $prev;
					}

					return $submission->valueFor($field)?->value();
				}, null);
			}

			$request = static::post('/comment-check', A::merge([
				'user_ip' => $visitor->ip(),
				'user_agent' => A::has($kirby->option('tobimori.dreamform.metadata.collect'), 'userAgent') ? $visitor->userAgent() : null,
				'referrer' => $request->header("Referer"),

				// send honeypot if used
				'honeypot_field_name' => $honeypotField = A::find($this->form()->guards(), fn ($guard) => $guard instanceof HoneypotGuard)?->fieldName(),
				'hidden_honeypot_field' => $honeypotField ? SubmissionPage::valueFromBody($honeypotField) : null,

				// language
				'blog_lang' => $kirby->multilang() ? $kirby->languages()->map(fn ($lang) => $lang->code())->join(', ') : null,
				'blog_charset' => 'UTF-8'
			], $content));

			if ($request->content() === 'true') {
				$submission->markAsSpam(true);
			}
		} catch (\Throwable $e) {
			// we don't want to block the submission if Akismet fails
		}
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
			[
				'data' => A::filter(A::merge([
					'api_key' => static::apiKey(),
					'blog' => "https://vierbeinerinnot.de", //App::instance()->site()->url(),
					'comment_type' => 'contact-form',
				], $data), fn ($value) => $value !== null)
			]
		);
	}

	/**
	 * Mark guard as available if an API key is set
	 */
	public static function isAvailable(): bool
	{
		if (
			!static::apiKey() ||
			!A::has(App::instance()->option('tobimori.dreamform.metadata.collect'), 'ip')
		) {
			return false;
		}

		return static::cache('verify-key', function () {
			$request = static::post('/verify-key', ['comment_type' => null]);
			return $request->code() === 200;
		});
	}
}
