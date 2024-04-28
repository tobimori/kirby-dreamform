<?php

use tobimori\DreamForm\DreamForm;

return function () {
	$page = DreamForm::currentPage();

	$blueprint = [];
	$fields = [];
	if ($page?->intendedTemplate()?->name() === 'form') {
		$fields = $page->fields();
	} elseif ($page?->intendedTemplate()?->name() === 'submission') {
		$fields = $page->form()->fields();
	}

	foreach ($fields as $field) {
		$blueprint[$field->key()] = $field->submissionBlueprint() ?? false;
	}

	return [
		'title' => 'dreamform.submission',
		'navigation' => [
			'status' => 'all',
			'sortBy' => 'sortDate desc'
		],
		'image' => [
			'icon' => 'archive',
			'back' => '#fafafa',
			'query' => 'page.gravatar()'
		],
		'options' => [
			'create' => false,
			'preview' => false,
			'changeSlug' => false,
			'changeStatus' => false,
			'duplicate' => false,
			'changeTitle' => false,
			'update' => false,
			'move' => false
		],
		'status' => [
			'draft' => false,
			'unlisted' => true,
			'listed' => false,
		],
		'columns' => [
			'main' => [
				'width' => '2/3',
				'fields' => $blueprint
			],
			'sidebar' => [
				'width' => '1/3',
				'sections' => [
					'submission' => [
						'type' => 'dreamform-submission'
					]
				]
			],
		]
	];
};
