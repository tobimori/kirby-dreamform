<?php

namespace tobimori\DreamForm\Panel\Controller\Dialog;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Panel\Controller\DialogController;
use Kirby\Panel\Field;
use Kirby\Panel\Ui\Dialog\FormDialog;
use Kirby\Toolkit\Str;
use Kirby\Toolkit\V;
use tobimori\DreamForm\Support\License;

/**
 * Dialog to activate DreamForm with a license key
 */
class LicenseActivateDialogController extends DialogController
{
	public function fields(): array
	{
		$system = $this->kirby->system();
		$local = $system->isLocal();

		return [
			'domain' => [
				'label' => $this->i18n('dreamform.license.activate.label'),
				'type' => 'info',
				'theme' => $local ? 'warning' : 'info',
				'text' => $this->i18nHtml(
					'dreamform.license.activate.' . ($local ? 'local' : 'domain'),
					['domain' => $system->indexUrl()]
				),
			],
			'email' => Field::email(['required' => true]),
			'license' => [
				'label' => $this->i18n('dreamform.license.key.label'),
				'type' => 'text',
				'required' => true,
				'counter' => false,
				'placeholder' => 'DF-XXX-1234XXXXXXXXXXXXXXXXXXXX',
				'help' => $this->i18nHtml('dreamform.license.key.help'),
			],
		];
	}

	public function load(): FormDialog
	{
		return new FormDialog(
			fields: $this->fields(),
			submitButton: [
				'icon' => 'key',
				'text' => $this->i18n('dreamform.license.activate'),
				'theme' => 'love',
			]
		);
	}

	public function submit(): array
	{
		$email = $this->request->get('email');
		$license = $this->request->get('license');

		if (!V::email($email)) {
			throw new InvalidArgumentException($this->i18n('dreamform.license.error.email'));
		}

		if (!Str::startsWith($license, 'DF-STD-') && !Str::startsWith($license, 'DF-ENT-')) {
			throw new InvalidArgumentException($this->i18n('dreamform.license.error.key'));
		}

		License::downloadLicense(email: $email, license: $license);

		return [
			'message' => 'License activated successfully!',
		];
	}
}
