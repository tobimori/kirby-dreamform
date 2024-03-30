<?php

use Kirby\Cms\App;
use tobimori\DreamForm\Support\License;

return [
	'dreamform-license' => [
		'computed' => [
			'local' => function () {
				return App::instance()->system()->isLocal();
			},
			'activated' => function () {
				return License::fromDisk()->isValid();
			}
		]
	]
];
