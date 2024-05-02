<?php

namespace tobimori\DreamForm;

use Kirby\Cache\Cache;
use Kirby\Cms\App;
use tobimori\DreamForm\Exceptions\PerformerException;
use tobimori\DreamForm\Models\FormPage;
use tobimori\DreamForm\Support\HasCache;

/**
 * Performer run something on submission.
 * Base class for all actions & guards.
 */
abstract class Performer
{
	use HasCache;

	/**
	 * Cancel the form submission
	 *
	 * The form will be shown as failed to the user and the error message will be displayed
	 */
	protected function cancel(string $message = null, bool $public = false, array|bool $log = true): void
	{
		throw new PerformerException(
			performer: $this,
			message: $message,
			public: $public,
			log: $log
		);
	}

	/**
	 * Silently cancel the form submission
	 *
	 * The form will be shown as successful to the user, except if debug mode is enabled
	 */
	protected function silentCancel(string $message = null, array|bool $log = true): void
	{
		throw new PerformerException(
			performer: $this,
			message: $message,
			silent: true,
			log: $log
		);
	}

	/**
	 * Returns the form the performer is being run on
	 */
	abstract public function form(): FormPage;

	/**
	 * Run the action
	 */
	abstract public function run(): void;

	/**
	 * Returns true if the performer is available
	 *
	 * Use this to disable performers based on configuration or other factors
	 */
	public static function isAvailable(): bool
	{
		return true;
	}

	abstract public static function type(): string;

	/**
	 * Get the performer's cache instance
	 */
	private static function cacheInstance(): Cache
	{
		return App::instance()->cache('tobimori.dreamform.performer');
	}
}
