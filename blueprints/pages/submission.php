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
				'fields' => $blueprint
			]
		]
	];
};
