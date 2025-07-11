<?php

use tobimori\DreamForm\DreamForm;

return function () {
	return [
		'type' => 'writer',
		'toolbar' => [
			'inline' => false
		],
		'marks' => DreamForm::option('marks'),
		'nodes' => [
			DreamForm::option('nodes'),
			'dreamformFormField' // custom node for field placeholders
		]
	];
};
