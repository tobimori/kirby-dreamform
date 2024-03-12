<?php

use tobimori\DreamForm\DreamForm;

return function () {
	$page = DreamForm::currentPage();

	$blueprint = [];
	if ($page?->intendedTemplate()?->name() === 'submission') {
		$fields = $page->parent()->fields();
		foreach ($fields as $field) {
			$blueprint[$field->key()] = $field->submissionBlueprint() ?? false;
		}
	}

	return [
		'title' => 'dreamform.submission',
		'image' => [
			'icon' => 'archive',
			'back' => '#fafafa',
			'query' => 'page.gravatar()'
		],
		'options' => [
			'preview' => false,
			'changeSlug' => false,
			'changeStatus' => false,
			'duplicate' => false,
			'changeTitle' => false,
			'update' => false,
		],
		'status' => [
			'draft' => false,
			'unlisted' => true,
			'listed' => false,
		],
		'columns' => [
			'sidebar' => [
				'width' => '1/3',
				'sections' => [],
			],
			'main' => [
				'width' => '2/3',
				'fields' => $blueprint
			]
		]
	];
};
