<?php

use tobimori\DreamForm\DreamForm;

return function () {
	// TODO: this should contain the custom nodes to select field from dropdown
	// but this needs Kirby 5 (probably), expect this with DreamForm 2.0

	return [
		'type' => 'writer',
		'toolbar' => [
			'inline' => false
		],
		'marks' => DreamForm::option('marks'),
	];
};
