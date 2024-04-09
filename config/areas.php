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
								'label' => t('dreamform.activate-plugin'),
								'type' => 'info',
								'theme' => ($isLocal = App::instance()->system()->isLocal()) ? 'warning' : 'info',
								'text' => tt(
									'dreamform.license-notice-' . ($isLocal ? 'local' : 'default'),
									['domain' => App::instance()->system()->indexUrl()]
								),
							],
							'email' => Field::email(['required' => true]),
							'license' => [
								'label' => t('dreamform.enter-license-key'),
								'type' => 'text',
								'required' => true,
								'counter' => false,
								'placeholder' => 'DF-XXX-1234XXXXXXXXXXXXXXXXXXXX',
								'help' => t('dreamform.license-key-help'),
							],
						],
						'submitButton' => [
							'icon' => 'key',
							'text' => t('dreamform.activate-license'),
							'theme' => 'love',
						]
					]
				],
				'submit' => function () {
					$body = App::instance()->request()->body();

					if (!V::email($body->get('email'))) {
						throw new Exception(t('dreamform.invalid-email'));
					}

					if (!Str::startsWith($body->get('license'), 'DF-STD-') && !Str::startsWith($body->get('license'), 'DF-ENT-')) {
						throw new Exception(t('dreamform.invalid-license'));
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
							'text' => t('dreamform.confirm-as-spam'),
							'submitButton' => [
								'text' => t('dreamform.report-as-spam'),
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
						'message' => t('dreamform.marked-as-spam'),
					];
				}
			],
			'submission/(:any)/mark-as-ham' => [
				'load' => function (string $path) {
					$submission = DreamForm::findPageOrDraftRecursive(Str::replace($path, '+', '/'));

					return [
						'component' => 'k-text-dialog',
						'props' => [
							'text' => t($submission->actionsDidRun() ? 'dreamform.confirm-as-ham' : 'dreamform.confirm-as-ham-unprocessed'),
							'submitButton' => [
								'text' => t('dreamform.report-as-ham'),
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
						$submission->updateState(['actionsDidRun' => true]);

						try {
							foreach ($submission->createActions() as $action) {
								$action->perform();
							}
						} catch (Exception $e) {
							return [
								'message' => t('dreamform.error-while-processing'),
								'error' => $e->getMessage(),
								'type' => 'error'
							];
						}
					}

					return [
						'message' => t('dreamform.marked-as-ham'),
					];
				}
			],
		]
	]
];
