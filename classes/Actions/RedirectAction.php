<?php

namespace tobimori\DreamForm\Actions;

use Kirby\Toolkit\Str;

/**
 * Action for redirecting the user to a success page after submitting.
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
			'icon' => 'share',
			'tabs' => []
		];
	}

	public function run(): void
	{
		$parse = Str::split($this->submission()->fields()->findBy('id', '89c4d3cd-59dc-41ac-93fe-ff3b8657fa9f')->value()->value(), '@')[0];

		switch ($parse) {
			case 'vermietung':
				$redirect = '/services/bueros/danke-bueroanmietung';
				break;
			case 'bookings':
				$redirect = '/services/kreativ-konferenzraeume/danke-kreativ-konferenzraeume';
				break;
			case 'hello+geschaeftsadresse':
				$redirect = '/services/geschaeftsadresse/danke-geschaeftsadresse';
				break;
			case 'hello+coworking':
				$redirect = '/services/coworking/danke-coworking';
				break;
			case 'hello+membership':
				$redirect = '/services/membership/danke-membership';
				break;
			case 'hello+sonstiges':
				$redirect = '/services/danke-service';
				break;
		}

		$response = kirby()->response();
		$response->redirect($redirect, 303);
	}
}
