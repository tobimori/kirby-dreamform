<?php

return function () {
	return [
		'type' => 'writer',
		'toolbar' => [
			'inline' => false,

		],
		'nodes' => [
			'heading',
			'horizontalRule',
			'paragraph',
			'contentPlaceholder',
		],
		'headings' => [
			'1', '2', '3'
		],
		'marks' => [
			'bold',
			'italic',
			'underline',
			'strikethrough',
		]
	];
};
