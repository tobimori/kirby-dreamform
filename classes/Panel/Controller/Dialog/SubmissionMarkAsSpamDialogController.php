<?php

namespace tobimori\DreamForm\Panel\Controller\Dialog;

use Kirby\Panel\Ui\Dialog\TextDialog;

/**
 * Dialog to report a submission as spam
 */
class SubmissionMarkAsSpamDialogController extends SubmissionDialogController
{
	public function load(): TextDialog
	{
		return new TextDialog(
			text: $this->i18nHtml('dreamform.submission.reportAsSpam.confirm'),
			submitButton: [
				'text' => $this->i18n('dreamform.submission.reportAsSpam.button'),
				'icon' => 'spam',
				'theme' => 'negative'
			]
		);
	}

	public function submit(): array
	{
		$this->submission->markAsSpam();

		return [
			'message' => $this->i18n('dreamform.submission.reportAsSpam.success'),
		];
	}
}
