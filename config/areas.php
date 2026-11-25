<?php

use tobimori\DreamForm\Panel\Controller\Dialog\FormFieldsDialogController;
use tobimori\DreamForm\Panel\Controller\Dialog\LicenseActivateDialogController;
use tobimori\DreamForm\Panel\Controller\Dialog\SubmissionMarkAsHamDialogController;
use tobimori\DreamForm\Panel\Controller\Dialog\SubmissionMarkAsSpamDialogController;
use tobimori\DreamForm\Panel\Controller\Dialog\SubmissionRunActionsDialogController;
use tobimori\DreamForm\Panel\Controller\Request\MailchimpFieldsRequestController;

return [
	'dreamform' => fn () => [
		'dialogs' => [
			'dreamform/activate' => LicenseActivateDialogController::class,
			'dreamform/form-fields' => FormFieldsDialogController::class,
			'submission/(:any)/mark-as-spam' => SubmissionMarkAsSpamDialogController::class,
			'submission/(:any)/mark-as-ham' => SubmissionMarkAsHamDialogController::class,
			'submission/(:any)/run-actions' => SubmissionRunActionsDialogController::class,
		],
		'requests' => [
			'dreamform.mailchimp.fields' => [
				'pattern' => 'dreamform/mailchimp/pages/(:any)/lists/(:any)/fields',
				'action' => MailchimpFieldsRequestController::class
			]
		]
	]
];
