<?php

use tobimori\DreamForm\DreamForm;

return function () {
	return [
		'type' => 'writer',
		'marks' => DreamForm::option('marks'),
		'inline' => true,
		'toolbar' => [
			'inline' => false
		]
	];
};
