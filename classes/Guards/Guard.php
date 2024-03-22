<?php

namespace tobimori\DreamForm\Guards;

use Kirby\Toolkit\Str;
use tobimori\DreamForm\Models\FormPage;
use tobimori\DreamForm\Performer;

abstract class Guard extends Performer
{
	/**
	 * Create a new Guard instance
	 */
	public function __construct(private FormPage $form)
	{
	}

	/**
	 * Returns the form the guard is being run on
	 */
	public function form(): FormPage
	{
		return $this->form;
	}

	public static function hasSnippet(): bool
	{
		return false;
	}

	public static function type(): string
	{
		return Str::kebab(Str::match(static::class, "/Guards\\\([a-zA-Z]+)Guard/")[1]);
	}
}
