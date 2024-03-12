<?php

use Kirby\Cms\App;
use Kirby\Toolkit\A;
use tobimori\DreamForm\DreamForm;

return function () {
	$page = DreamForm::currentPage();

	// this fixes some edge cases where the current page
	// can't be determined from the url path
	if ($page?->intendedTemplate()->name() !== 'form') {
		return null;
	}

	$columns = [];
	foreach ($page?->fields()->limit(4) as $field) {
		$columns[$field->key()] = [
			'label' => $field->block()->label()->value(),
		];
	}

	return [
		'label' => t('dreamform.submissions'),
		'type' => 'pages',
		'empty' => 'dreamform.empty-submissions',
		'template' => 'submission',
		'layout' => 'table',
		'create' => false,
		'image' => App::instance()->option('tobimori.dreamform.integrations.gravatar'),
		'text' => false,
		'search' => true,
		'sortBy' => 'sortDate desc',
		'columns' => A::merge([
			'date' => [
				'label' => t('dreamform.submitted-at'),
				'type' => 'html',
				'value' => '<a href="{{ page.panel.url }}">{{ page.title }}</a>',
				'mobile' => true
			],
		], $columns)
	];
};
