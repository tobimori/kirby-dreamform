<?php

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
		$columns[$field->field()->key()->value()] = [
			'label' => $field->field()->label()->value(),
		];
	}

	return [
		'label' => t('dreamform.submissions'),
		'type' => 'pages',
		'empty' => 'dreamform.empty-submissions',
		'template' => 'submission',
		'layout' => 'table',
		'create' => false,
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
