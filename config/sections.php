<?php

use Kirby\Cms\App;
use Kirby\Exception\Exception;
use tobimori\DreamForm\Support\License;

return [
	'dreamform-submission' => [
		'computed' => [
			'page' => function () {
				if ($this->model()->intendedTemplate()->name() !== 'submission') {
					throw new Exception('[DreamForm] This section can only be used on submission pages');
				}

				return $this->model();
			},
			'isSpam' => function () {
				return $this->model()->isSpam();
			},
			'isPartial' => function () {
				return !$this->model()->isFinished();
			},
			'log' => function () {
				return $this->model()->log()->toArray();
			}
		]
	],
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
