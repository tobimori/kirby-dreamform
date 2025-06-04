<?php

namespace tobimori\DreamForm\Guards;

use Kirby\Toolkit\Str;
use tobimori\DreamForm\Models\FormPage;
use tobimori\DreamForm\Models\SubmissionPage;
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
	 * Additional post field validation check
	 */
	public function postValidation(SubmissionPage	$submission): void
	{
	}

	/**
	 * Run the guard
	 */
	public function run(): void
	{
		$this->precognitiveRun();
	}

	/**
	 * Precognitive run
	 */
	public function precognitiveRun(): void
	{
	}

	/**
	 * Reports the submission as spam to a third-party service
	 */
	public function reportSubmissionAsSpam(SubmissionPage $submission): void
	{
	}

	/**
	 * Reports the submission as ham to a third-party service
	 */
	public function reportSubmissionAsHam(SubmissionPage $submission): void
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
}
