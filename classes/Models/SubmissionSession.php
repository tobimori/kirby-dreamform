<?php

namespace tobimori\DreamForm\Models;

use Kirby\Cms\App;
use Kirby\Toolkit\Str;
use tobimori\DreamForm\DreamForm;
use tobimori\DreamForm\Models\SubmissionPage;
use tobimori\DreamForm\Storage\SubmissionSessionStorage;
use tobimori\DreamForm\Storage\SubmissionCacheStorage;
use tobimori\DreamForm\Support\Htmx;

trait SubmissionSession
{
	private static SubmissionPage|null $session = null;

	/**
	 * Store submission reference for retrieval
	 */
	public function storeSession(): static
	{
		$kirby = App::instance();
		$mode = DreamForm::option('mode', 'prg');
		$storage = $this->storage();

		// If using a session-aware storage handler, let it handle the reference
		if (
			$storage instanceof SubmissionSessionStorage ||
			$storage instanceof SubmissionCacheStorage
		) {
			$storage->storeReference();
		} else {
			// For PlainTextStorage (already persisted), store the uuid reference
			if ($mode === 'api' || (Htmx::isActive() && Htmx::isHtmxRequest())) {
				// In sessionless mode, the reference is passed via request body
				// Nothing to store server-side
			} else {
				// Store UUID in PHP session for PRG mode
				$kirby->session()->set(DreamForm::SESSION_KEY, $this->uuid()->toString());
			}
		}

		return static::$session = $this;
	}

	/**
	 * Reconstruct submission from data
	 */
	private static function reconstructSubmission(mixed $data): SubmissionPage|null
	{
		if (is_string($data)) {
			// It's a slug / uuid reference - submission exists on disk
			return DreamForm::findPageOrDraftRecursive($data);
		}

		if (is_array($data) && isset($data['type']) && $data['type'] === 'submission') {
			// It's submission metadata - reconstruct the submission
			$parent = DreamForm::findPageOrDraftRecursive($data['parent']);
			if ($parent) {
				return new SubmissionPage([
					'template' => $data['template'],
					'slug' => $data['slug'],
					'parent' => $parent,
				]);
			}
		}

		return null;
	}

	/**
	 * Clean up submission if appropriate
	 */
	private static function cleanupIfNeeded(SubmissionPage $submission): void
	{
		// Only clean up if submission is finished
		// Don't clean up on validation errors - they're expected during form filling
		if ($submission->isFinished()) {
			App::instance()->session()->remove(DreamForm::SESSION_KEY);

			$storage = $submission->storage();
			if (method_exists($storage, 'cleanup')) {
				/** @var SubmissionSessionStorage|SubmissionCacheStorage $storage */
				$storage->cleanup();
			}
		}
	}

	/**
	 * Pull submission from session
	 */
	public static function fromSession(): SubmissionPage|null
	{
		// Return cached instance if available
		if (static::$session) {
			return static::$session;
		}

		$kirby = App::instance();
		$mode = DreamForm::option('mode', 'prg');

		// Determine where to look for data
		if ($mode === 'api' || ($mode === 'htmx' && Htmx::isHtmxRequest())) {
			// Get from request body
			$raw = $kirby->request()->body()->get('dreamform:session');
			if (!$raw || $raw === 'null') {
				return null;
			}

			$id = Htmx::decrypt($raw);
			if (Str::startsWith($id, 'page://')) {
				$data = $id;
			} else {
				// Get from cache
				$data = $kirby->cache('tobimori.dreamform.sessionless')->get($id);
			}
		} else {
			// Get from PHP session
			$data = $kirby->session()->get(DreamForm::SESSION_KEY);
		}

		if (!$data) {
			return null;
		}

		// Reconstruct submission
		$submission = static::reconstructSubmission($data);
		if (!($submission instanceof SubmissionPage)) {
			return null;
		}

		static::$session = $submission;
		static::cleanupIfNeeded($submission);

		return static::$session;
	}
}
