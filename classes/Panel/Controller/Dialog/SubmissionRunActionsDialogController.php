<?php

namespace tobimori\DreamForm\Panel\Controller\Dialog;

use Kirby\Panel\Ui\Dialog\TextDialog;

/**
 * Dialog to run the current workflow of the form on a submission again
 */
class SubmissionRunActionsDialogController extends SubmissionDialogController
{
	public function load(): TextDialog
	{
		return new TextDialog(
			text: $this->i18nHtml('dreamform.submission.runActions.confirm'),
			submitButton: [
				'text' => $this->i18n('dreamform.submission.runActions.button'),
				'icon' => 'play',
				'theme' => 'positive'
			]
		);
	}

	public function submit(): array
	{
		$this->submission->handleActions(force: true);

		return [
			'message' => $this->i18n('dreamform.submission.runActions.success'),
		];
	}
}
