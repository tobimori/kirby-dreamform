<?php

namespace tobimori\DreamForm\Actions;

use Kirby\Cms\App;
use Kirby\Cms\Block;
use tobimori\DreamForm\Exceptions\SuccessException;
use tobimori\DreamForm\Models\SubmissionPage;
use tobimori\DreamForm\Performer;
use Kirby\Toolkit\Str;

/**
 * Base class for all actions.
 */
abstract class Action extends Performer
{
	/**
	 * Create a new Action instance.
	 */
	public function __construct(private Block $block, private SubmissionPage $submission)
	{
	}

	/**
	 * Returns the action configuration
	 */
	public function block(): Block
	{
		return $this->block;
	}

	/**
	 * Finish the form submission early
	 */
	protected function success(): void
	{
		throw new SuccessException();
	}

	/**
	 * Returns the Blocks fieldset blueprint for the actions' settings
	 */
	abstract public static function blueprint(): array;

	/**
	 * Returns the actions' blueprint group
	 */
	public static function group(): string
	{
		return 'actions';
	}

	/**
	 * Use the action cache
	 */
	public static function cache(string $key, callable $callback): mixed
	{
		$cache = App::instance()->cache('tobimori.dreamform.actions');
		if (!$cache) {
			return $callback();
		}

		$value = $cache->get($key);
		if ($value === null) {
			$value = $callback();
			$cache->set($key, $value, 10);
		}

		return $value;
	}

	public static function type(): string
	{
		return Str::kebab(Str::match(static::class, "/Actions\\\([a-zA-Z]+)Action/")[1]);
	}
}
