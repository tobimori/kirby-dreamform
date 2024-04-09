<?php

namespace tobimori\DreamForm;

use Kirby\Cache\Cache;
use Kirby\Cms\App;
use tobimori\DreamForm\Exceptions\PerformerException;
use tobimori\DreamForm\Exceptions\SilentPerformerException;
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
	 * The form will be shown as failed to the user
	 * and the error message will be displayed
	 */
	protected function cancel(string $message = null, bool $public = false): void
	{
		$message ??= t('dreamform.generic-error');

		if (!$public && !DreamForm::debugMode()) {
			throw new PerformerException(t('dreamform.generic-error'));
		}

		throw new PerformerException($message);
	}

	/**
	 * Silently cancel the form submission
	 *
	 * The form will be shown as successful to the user,
	 * except if debug mode is enabled
	 */
	protected function silentCancel(string $message = null): void
	{
		$message ??= t('dreamform.generic-error');

		if (DreamForm::debugMode()) {
			throw new PerformerException($message);
		}

		throw new SilentPerformerException($message);
	}

	/**
	 * Perform the actual action
	 * @internal
	 */
	public function perform(): void
	{
		$this->run();
	}

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
