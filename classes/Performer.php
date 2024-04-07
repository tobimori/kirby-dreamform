<?php

namespace tobimori\DreamForm;

use Kirby\Cache\Cache;
use Kirby\Cms\App;
use tobimori\DreamForm\Exceptions\PerformerException;
use tobimori\DreamForm\Exceptions\SilentPerformerException;

/**
 * Performer run something on submission.
 * Base class for all actions & guards.
 */
abstract class Performer
{
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

	/**
	 * Get/set a value for the performer cache
	 */
	protected static function cache(string $key, callable $callback, int $minutes = 10): mixed
	{
		if (!($cache = static::cacheInstance())) {
			return $callback();
		}

		$key = static::type() . '.' . $key;
		$value = $cache->get($key);
		if ($value === null) {
			$value = $callback();
			$cache->set($key, $value, $minutes);
		}

		return $value;
	}

	/**
	 * Set a value for the performer cache
	 */
	protected static function setCache(string $key, mixed $value, int $minutes = 10): bool
	{
		if (!($cache = static::cacheInstance())) {
			return false;
		}

		$key = static::type() . '.' . $key;
		return $cache->set($key, $value, $minutes);
	}

	/**
	 * Get a value from the performer cache
	 */
	protected static function getCache(string $key): mixed
	{
		if (!($cache = static::cacheInstance())) {
			return null;
		}

		$key = static::type() . '.' . $key;
		return $cache->get($key);
	}
}
