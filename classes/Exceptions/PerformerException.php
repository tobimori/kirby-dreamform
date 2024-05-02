<?php

namespace tobimori\DreamForm\Exceptions;

use Kirby\Exception\Exception;
use tobimori\DreamForm\DreamForm;
use tobimori\DreamForm\Models\FormPage;
use tobimori\DreamForm\Models\SubmissionPage;
use tobimori\DreamForm\Performer;

/**
 * Drop the form submission with a visible error message
 */
class PerformerException extends Exception
{
	public const GENERIC_ERROR = 'dreamform.submission.error.generic';

	public function __construct(
		protected Performer $performer,
		string|null $message,
		protected bool $public = false,
		protected bool $silent = false,
		protected SubmissionPage|null $submission = null
	) {
		$translated = t($message ?? self::GENERIC_ERROR);

		if ($this->submission()) {
			$this->submission()->addLogEntry([
				'text' => $message ?? self::GENERIC_ERROR,
				'template' => [
					'type' => $this->performer->type(),
				]
			], type: 'error', icon: 'alert', title: "dreamform.submission.log.error");
		}

		parent::__construct($this->isPublic() ? $translated : t(self::GENERIC_ERROR));
	}

	public function submission(): SubmissionPage|false
	{
		if (!$this->submission) {
			return false;
		}

		return $this->submission;
	}

	public function form(): FormPage
	{
		return $this->performer->form();
	}

	public function shouldContinue(): bool
	{
		return $this->form()->continueOnError()->toBool();
	}

	public function isSilent(): bool
	{
		return $this->silent && !DreamForm::debugMode();
	}

	public function isPublic(): bool
	{
		return $this->public || DreamForm::debugMode();
	}
}
