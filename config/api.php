<?php

use Kirby\Toolkit\Str;
use tobimori\DreamForm\Actions\MailchimpAction;
use tobimori\DreamForm\DreamForm;

return [
	'routes' => fn () => [
		[
			'pattern' => '/dreamform/object/mailchimp/pages/(:any)/(:any)',
			'action' => fn ($path, $list) => MailchimpAction::fieldMapping(DreamForm::findPageOrDraftRecursive(Str::replace($path, '+', '/')), $list),
		]
	]
];
