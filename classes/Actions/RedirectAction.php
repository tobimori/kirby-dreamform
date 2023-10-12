<?php

namespace tobimori\DreamForm\Actions;

use Kirby\Toolkit\Str;

/**
 * Action for redirecting the user to a success page after submitting.
 *
 * @package tobimori\DreamForm\Actions
 */
class RedirectAction extends Action
{
	public static function blueprint(): array
	{
		return [
			'title' => t('redirect-action'),
			'preview' => 'fields',
			'wysiwyg' => true,
			'icon' => 'shuffle',
			'tabs' => []
		];
	}

	public function run()
	{
		$parse = Str::split($this->submission()->fields()->findBy('id', '89c4d3cd-59dc-41ac-93fe-ff3b8657fa9f')->value()->value(), '@')[0];

		switch ($parse) {
			case 'vermietung':
				$redirect = '/services/bueros/danke';
				break;
			case 'bookings':
				$redirect = '/services/kreativ-konferenzraeume/danke';
				break;
			case 'hello+geschaeftsadresse':
				$redirect = '/services/geschaeftsadresse/danke';
				break;
			case 'hello+coworking':
				$redirect = '/services/coworking/danke';
				break;
			case 'hello+membership':
				$redirect = '/services/membership/danke';
				break;
			case 'hello+sonstiges':
				$redirect = '/services/danke';
				break;
		}

		$response = kirby()->response();
		$response->redirect("{$redirect}?form=sent", 303);

		return [
			'redirect' => "{$redirect}?form=sent"
		];
	}
}
