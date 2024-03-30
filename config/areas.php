<?php

use Kirby\Cms\App;
use Kirby\Panel\Field;
use Kirby\Toolkit\Str;
use Kirby\Toolkit\V;
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
								'theme' => $isLocal = App::instance()->system()->isLocal() ? 'warning' : 'info',
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
			]
		]
	]
];
