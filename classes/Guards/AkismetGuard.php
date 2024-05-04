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
	 * Returns the content to be sent to Akismet
	 */
	protected function contentForSubmission(SubmissionPage $submission): array
	{
		$content = [];
		foreach (DreamForm::option('guards.akismet.fields', []) as $key => $fields) {
			$content[$key] = A::reduce($fields, function ($prev, $field) use ($submission) {
				if ($prev !== null) {
					return $prev;
				}

				return $submission->valueFor($field)?->value();
			}, null);
		}

		return $content;
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
			$request = static::post('/comment-check', A::merge([
				// send metadata
				'user_ip' => $visitor->ip(),
				'user_agent' => A::has(DreamForm::option('metadata.collect'), 'userAgent') ? $visitor->userAgent() : null,
				'referrer' => $request->header("Referer"),

				// send honeypot if used
				'honeypot_field_name' => $honeypotField = A::find($this->form()->guards(), fn ($guard) => $guard instanceof HoneypotGuard)?->fieldName(),
				'hidden_honeypot_field' => $honeypotField ? SubmissionPage::valueFromBody($honeypotField) : null,
			], $this->contentForSubmission($submission)));

			if ($request->content() === 'true') {
				$submission->markAsSpam(true);
			}
		} catch (\Throwable $e) {
			// we don't want to block the submission if Akismet fails
		}
	}

	/**
	 * Returns the content to be reported to Akismet
	 */
	protected function reportContentForSubmission(SubmissionPage $submission): array
	{
		return A::merge([
			'comment_author' => $submission->metadata()->name()?->value(),
			'comment_author_email' => $submission->metadata()->email()?->value(),
			'comment_author_url' => $submission->metadata()->website()?->value(),
			'comment_content' => $submission->message()->value()
		], $this->contentForSubmission($submission));
	}

	/**
	 * Reports the submission as spam to Akismet
	 */
	public function reportSubmissionAsSpam(SubmissionPage $submission): void
	{
		static::post('/submit-spam', $this->reportContentForSubmission($submission));
	}

	/**
	 * Reports the submission as ham to Akismet
	 */
	public function reportSubmissionAsHam(SubmissionPage $submission): void
	{
		static::post('/submit-ham', $this->reportContentForSubmission($submission));
	}

	/**
	 * Returns the Akismet API key
	 */
	protected static function apiKey(): string|null
	{
		return DreamForm::option('guards.akismet.apiKey');
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
		$kirby = App::instance();

		return Remote::post(
			static::apiUrl() . $url,
			[
				'data' => A::filter(A::merge([
					'api_key' => static::apiKey(),
					'blog' => App::instance()->site()->url(),
					'comment_type' => 'contact-form',
					'blog_lang' => $kirby->multilang() ? $kirby->languages()->map(fn ($lang) => $lang->code())->join(', ') : null,
					'blog_charset' => 'UTF-8'
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
			!A::has(DreamForm::option('metadata.collect'), 'ip')
		) {
			return false;
		}

		return static::cache('verify-key', function () {
			$request = static::post('/verify-key');
			return $request->code() === 200;
		});
	}
}
