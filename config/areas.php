<?php

use Kirby\Cms\App;
use Kirby\Panel\Field;
use Kirby\Toolkit\Str;
use Kirby\Toolkit\V;
use tobimori\DreamForm\DreamForm;
use tobimori\DreamForm\Support\License;

return [
	'dreamform' => fn () => [
		'dialogs' => [
			'dreamform/activate' => [
				'load' => fn () => [
					'component' => 'k-form-dialog',
					'props' => [
						'fields' => [
							'domain' => [
								'label' => t('dreamform.license.activate.label'),
								'type' => 'info',
								'theme' => ($isLocal = App::instance()->system()->isLocal()) ? 'warning' : 'info',
								'text' => tt(
									'dreamform.license.activate.' . ($isLocal ? 'local' : 'domain'),
									['domain' => App::instance()->system()->indexUrl()]
								),
							],
							'email' => Field::email(['required' => true]),
							'license' => [
								'label' => t('dreamform.license.key.label'),
								'type' => 'text',
								'required' => true,
								'counter' => false,
								'placeholder' => 'DF-XXX-1234XXXXXXXXXXXXXXXXXXXX',
								'help' => t('dreamform.license.key.help'),
							],
						],
						'submitButton' => [
							'icon' => 'key',
							'text' => t('dreamform.license.activate'),
							'theme' => 'love',
						]
					]
				],
				'submit' => function () {
					$body = App::instance()->request()->body();

					if (!V::email($body->get('email'))) {
						throw new Exception(t('dreamform.license.error.email'));
					}

					if (!Str::startsWith($body->get('license'), 'DF-STD-') && !Str::startsWith($body->get('license'), 'DF-ENT-')) {
						throw new Exception(t('dreamform.license.error.key'));
					}

					License::downloadLicense(
						email: $body->get('email'),
						license: $body->get('license')
					);

					return [
						'message' => 'License activated successfully!',
					];
				}
			],
			'submission/(:any)/mark-as-spam' => [
				'load' => function (string $path) {
					return [
						'component' => 'k-text-dialog',
						'props' => [
							'text' => t('dreamform.submission.reportAsSpam.confirm'),
							'submitButton' => [
								'text' => t('dreamform.submission.reportAsSpam.button'),
								'icon'  => 'spam',
								'theme' => 'negative'
							],
						]
					];
				},
				'submit' => function (string $path) {
					$submission = DreamForm::findPageOrDraftRecursive(Str::replace($path, '+', '/'));
					$submission = $submission->markAsSpam();

					return [
						'message' => t('dreamform.submission.reportAsSpam.success'),
					];
				}
			],
			'submission/(:any)/mark-as-ham' => [
				'load' => function (string $path) {
					$submission = DreamForm::findPageOrDraftRecursive(Str::replace($path, '+', '/'));

					return [
						'component' => 'k-text-dialog',
						'props' => [
							'text' => t($submission->actionsDidRun() ? 'dreamform.submission.reportAsHam.confirm.default' : 'dreamform.submission.reportAsHam.confirm.unprocessed'),
							'submitButton' => [
								'text' => t('dreamform.submission.reportAsHam.button'),
								'icon'  => 'shield-check',
								'theme' => 'positive'
							],
						]
					];
				},
				'submit' => function (string $path) {
					$submission = DreamForm::findPageOrDraftRecursive(Str::replace($path, '+', '/'));
					$submission = $submission->markAsHam();

					if (!$submission->actionsDidRun()) {
						$submission->updateState(['actionsdidrun' => true]);
						$submission->handleActions(force: true);
					}

					return [
						'message' => t('dreamform.submission.reportAsHam.success'),
					];
				}
			],
			'submission/(:any)/run-actions' => [
				'load' => function () {
					return [
						'component' => 'k-text-dialog',
						'props' => [
							'text' => t('dreamform.submission.runActions.confirm'),
							'submitButton' => [
								'text' => t('dreamform.submission.runActions.button'),
								'icon'  => 'play',
								'theme' => 'positive'
							],
						]
					];
				},
				'submit' => function (string $path) {
					$submission = DreamForm::findPageOrDraftRecursive(Str::replace($path, '+', '/'));
					$submission = $submission->handleActions(force: true);

					return [
						'message' => t('dreamform.submission.runActions.success'),
					];
				}
			],
		]
	]
];
