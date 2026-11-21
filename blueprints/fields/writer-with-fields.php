<?php

use tobimori\DreamForm\DreamForm;

return function () {
	$nodes = DreamForm::option('nodes');

	return [
		'type' => 'writer',
		'toolbar' => [
			'inline' => false
		],
		'marks' => DreamForm::option('marks'),
		// custom node for field placeholders
		'nodes' => match (true) {
			is_array($nodes) => [...$nodes, 'dreamformFormField'],
			$nodes === false => ['dreamformFormField'],
			default => $nodes
		},
		'agents' => ['as' => 'dreamform-writer']
	];
};
