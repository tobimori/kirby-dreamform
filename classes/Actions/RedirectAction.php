<?php

namespace tobimori\DreamForm\Actions;

use Kirby\Toolkit\Str;

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

	public function run(): void
	{
		$parse = Str::split($this->submission()->fields()->findBy('id', '89c4d3cd-59dc-41ac-93fe-ff3b8657fa9f')->value()->value(), '@')[0];
		$response = kirby()->response();
		$response->redirect('/danke', 303);
		return;

		switch ($parse) {
			case 'vermietung':
				$redirect = '/danke/vermietung';
				break;
			case 'bookings':
				$redirect = '/danke/bookings';
				break;
			case 'hello+virtualoffice':
				$redirect = '/danke/virtualoffice';
				break;
			case 'hello+coworking':
				$redirect = '/danke/coworking';
				break;
			case 'hello+membership':
				$redirect = '/danke/membership';
				break;
			case 'hello+sonstiges':
				$redirect = '/danke/sonstiges';
				break;
		}
	}
}
