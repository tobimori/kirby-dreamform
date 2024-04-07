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
						'component' => 'k-remove-dialog',
						'props' => [
							'text' => t('dreamform.confirm-as-spam'),
							'submitButton' => [
								'text' => t('dreamform.mark-as-spam'),
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
				'load' => fn (string $path) => [
					'component' => 'k-remove-dialog',
				]
			],
		]
	]
];
