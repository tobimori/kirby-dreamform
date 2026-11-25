<?php

namespace tobimori\DreamForm\Panel\Controller\Dialog;

use Kirby\Panel\Ui\Dialog\TextDialog;

/**
 * Dialog to report a submission as ham and to run
 * the actions that the spam filter has skipped
 */
class SubmissionMarkAsHamDialogController extends SubmissionDialogController
{
	public function load(): TextDialog
	{
		return new TextDialog(
			text: $this->i18nHtml(
				$this->submission->actionsDidRun()
					? 'dreamform.submission.reportAsHam.confirm.default'
					: 'dreamform.submission.reportAsHam.confirm.unprocessed'
			),
			submitButton: [
				'text' => $this->i18n('dreamform.submission.reportAsHam.button'),
				'icon' => 'shield-check',
				'theme' => 'positive'
			]
		);
	}

	public function submit(): array
	{
		$submission = $this->submission->markAsHam();

		if (!$submission->actionsDidRun()) {
			$submission->updateState(['actionsdidrun' => true]);
			$submission->handleActions(force: true);
		}

		return [
			'message' => $this->i18n('dreamform.submission.reportAsHam.success'),
		];
	}
}
