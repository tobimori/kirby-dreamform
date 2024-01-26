<?php

use Kirby\Cms\App;
use Kirby\Toolkit\Str;

return function () {
	$path = App::instance()->request()->path()->data()[2];
	$page = App::instance()->site()->findPageOrDraft(Str::replace($path, '+', '/'));

	if ($page->intendedTemplate()->name() !== 'submission') {
		return false;
	}

	$blueprint = [];
	$fields = $page->parent()->fields();
	foreach ($fields as $field) {
		$key = $field->field()->key()->or($field->field()->id())->value();
		$blueprint[$key] = $field->submissionBlueprint() ?? false;
	}

	return [
		'title' => 'submission',
		'image' => [
			'icon' => 'archive',
			'query' => 'icon'
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
