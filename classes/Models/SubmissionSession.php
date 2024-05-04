<?php

namespace tobimori\DreamForm\Models;

use Kirby\Cms\App;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;
use tobimori\DreamForm\DreamForm;
use tobimori\DreamForm\Models\SubmissionPage;
use tobimori\DreamForm\Support\Htmx;

trait SubmissionSession
{
	private static SubmissionPage|null $session = null;

	/**
	 * Store submission in session for use with PRG pattern
	 */
	public function storeSession(): static
	{
		$kirby = App::instance();
		$mode = DreamForm::option('mode', 'prg');
		if ($mode === 'api' || Htmx::isActive() && Htmx::isHtmxRequest()) {
			return $this->storeSessionlessCache();
		}

		$kirby->session()->set(
			DreamForm::SESSION_KEY,
			// if the page exists on disk, we store the UUID only so we can save files since they can't be serialized
			$this->exists() ? $this->slug() : $this
		);

		return static::$session = $this;
	}

	public function storeSessionlessCache(): static
	{
		if (A::has(['prg', 'htmx'], DreamForm::option('mode', 'prg')) && !Htmx::isHtmxRequest()) {
			return $this->storeSession();
		}

		if (!$this->exists()) {
			App::instance()->cache('tobimori.dreamform.sessionless')->set($this->slug(), serialize($this), 60 * 24);
		}

		return static::$session = $this;
	}

	/**
	 * Pull submission from session
	 */
	public static function fromSession(): SubmissionPage|null
	{
		$kirby = App::instance();
		$mode = DreamForm::option('mode', 'prg');
		if ($mode === 'api' || $mode === 'htmx' && Htmx::isHtmxRequest()) {
			return static::fromSessionlessCache();
		}

		if (static::$session) {
			return static::$session;
		}

		$session = $kirby->session()->get(DreamForm::SESSION_KEY, null);
		if (is_string($session)) { // if the page exists on disk, we store the UUID only so we can save files
			$session = DreamForm::findPageOrDraftRecursive("page://{$session}");
		}

		if (!($session instanceof SubmissionPage)) {
			return null;
		}

		static::$session = $session;

		// remove it from the session for subsequent loads
		if (
			static::$session && ( // if the session exists
				static::$session->isFinished() // & if the submission is finished
				|| (static::$session->currentStep() === 1 && !static::$session->isSuccessful()) // or if it's the first step and not successful
			)
		) {
			$kirby->session()->remove(DreamForm::SESSION_KEY);
		}

		return static::$session;
	}

	/**
	 * Get submission from sessionless cache
	 */
	public static function fromSessionlessCache(): SubmissionPage|null
	{
		$kirby = App::instance();
		if (DreamForm::option('mode', 'prg') === 'prg' && !Htmx::isHtmxRequest()) {
			return static::fromSession();
		}

		if (static::$session) {
			return static::$session;
		}

		$raw = $kirby->request()->body()->get('dreamform:session');
		if (!$raw || $raw === 'null') {
			return null;
		}

		$id = Htmx::decrypt($raw);
		if (Str::startsWith($id, 'page://')) {
			static::$session = DreamForm::findPageOrDraftRecursive($id);

			if (static::$session) {
				return static::$session;
			}
		}

		$cache = $kirby->cache('tobimori.dreamform.sessionless');
		$serialized = $cache->get($id);
		if ($serialized) {
			$submission = unserialize($serialized);
			if ($submission instanceof SubmissionPage) {
				static::$session = $submission;

				// remove it from the session for subsequent loads
				if (
					$submission->isFinished() // & if the submission is finished
					|| ($submission->currentStep() === 1 && !$submission->isSuccessful()) // or if it's the first step and not successful
				) {
					$cache->remove($id);
				}
			}
		}

		return static::$session;
	}
}
