<?php

namespace tobimori\DreamForm\Actions;

use Kirby\Cms\Block;
use tobimori\DreamForm\Exceptions\SuccessException;
use tobimori\DreamForm\Models\SubmissionPage;
use tobimori\DreamForm\Performer;
use Kirby\Toolkit\Str;
use tobimori\DreamForm\Exceptions\PerformerException;
use tobimori\DreamForm\Models\FormPage;
use tobimori\DreamForm\Models\Log\SubmissionLogEntry;

/**
 * Base class for all actions.
 */
abstract class Action extends Performer
{
	/**
	 * Create a new Action instance.
	 * @internal
	 */
	public function __construct(private Block $block, private SubmissionPage $submission, private bool $force = false)
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
	 * Returns true if the action is executed forcibly
	 */
	public function isForced(): bool
	{
		return $this->force;
	}

	/**
	 * Returns the base log settings for the action
	 */
	protected function logSettings(): array|bool
	{
		return true;
	}

	/**
	 * Create an action log entry
	 */
	protected function log(array $data, string $type = null, string $icon = null, string $title = null): SubmissionLogEntry
	{
		return $this->submission()->addLogEntry($data, $type, $icon, $title);
	}

	/**
	 * Cancel the form submission
	 *
	 * The form will be shown as failed to the user and the error message will be displayed
	 */
	protected function cancel(string $message = null, bool $public = false, array|bool $log = null): void
	{
		throw new PerformerException(
			performer: $this,
			message: $message,
			public: $public,
			force: $this->isForced(),
			submission: $this->submission(),
			log: $log ?? $this->logSettings()
		);
	}

	/**
	 * Silently cancel the form submission
	 *
	 * The form will be shown as successful to the user, except if debug mode is enabled
	 */
	protected function silentCancel(string $message = null, array|bool $log = null): void
	{
		throw new PerformerException(
			performer: $this,
			message: $message,
			silent: true,
			force: $this->isForced(),
			submission: $this->submission(),
			log: $log ?? $this->logSettings()
		);
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

	/**
	 * Returns the action type string
	 */
	public static function type(): string
	{
		return Str::kebab(Str::match(static::class, "/Actions\\\([a-zA-Z]+)Action/")[1]);
	}
}
