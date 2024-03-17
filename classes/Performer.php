<?php

namespace tobimori\DreamForm;

use Kirby\Toolkit\Str;
use tobimori\DreamForm\Exceptions\PerformerException;
use tobimori\DreamForm\Exceptions\SilentPerformerException;
use tobimori\DreamForm\Models\FormPage;
use tobimori\DreamForm\Models\SubmissionPage;

/**
 * Performer run something on submission.
 * Base class for all actions & guards.
 */
abstract class Performer
{
	/**
	 * Create a new Performer instance
	 */
	public function __construct(private SubmissionPage $submission)
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
	abstract public function run();

	/**
	 * Returns true if the performer is available
	 *
	 * Use this to disable performers based on configuration or other factors
	 */
	public static function isAvailable(): bool
	{
		return true;
	}

	public static function type(): string
	{
		return Str::kebab(Str::match(static::class, "/Guards\\\([a-zA-Z]+)Guard/")[1]);
	}
}
