<?php

namespace tobimori\DreamForm\Panel;

use Kirby\Exception\Exception;
use Kirby\Form\Field\DisplayField;
use tobimori\DreamForm\Models\SubmissionPage;

/**
 * Shows the state and the log of a submission
 */
class SubmissionField extends DisplayField
{
	public function props(): array
	{
		$submission = $this->model();

		if (!($submission instanceof SubmissionPage)) {
			throw new Exception('[DreamForm] This field can only be used on submission pages');
		}

		return [
			...parent::props(),
			'path' => $submission->panel()->id(),
			'isSpam' => $submission->isSpam(),
			'isPartial' => !$submission->isFinished(),
			'log' => $submission->log()->toArray()
		];
	}
}
