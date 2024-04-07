<?php

namespace tobimori\DreamForm\Actions;

use Kirby\Cms\App;
use Kirby\Cms\Block;
use tobimori\DreamForm\Exceptions\SuccessException;
use tobimori\DreamForm\Models\SubmissionPage;
use tobimori\DreamForm\Performer;
use Kirby\Toolkit\Str;
use tobimori\DreamForm\Models\FormPage;

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
	 * Returns the submission the performer is being run on
	 */
	public function submission(): SubmissionPage
	{
		return $this->submission;
	}

	/**
	 * Returns the form the performer is being run on
	 */
	public function form(): FormPage
	{
		return $this->submission()->form();
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
		return 'common';
	}

	public static function type(): string
	{
		return Str::kebab(Str::match(static::class, "/Actions\\\([a-zA-Z]+)Action/")[1]);
	}
}
