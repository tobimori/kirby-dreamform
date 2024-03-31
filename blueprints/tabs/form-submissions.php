<?php

use Kirby\Cms\App;
use Kirby\Toolkit\A;
use tobimori\DreamForm\DreamForm;

return function () {
	/** @var \tobimori\DreamForm\Models\FormPage $page */
	$page = DreamForm::currentPage();

	// this fixes some edge cases where the current page
	// can't be determined from the url path
	if ($page?->intendedTemplate()->name() !== 'form') {
		return null;
	}

	$columns = [];
	foreach ($page?->fields()->filterBy(fn ($field) => $field::hasValue())->limit(4) as $field) {
		$columns[$field->key()] = [
			'label' => $field->block()->label()->value(),
		];
	}

	return [
		'label' => t('dreamform.submissions'),
		'icon' =>  'archive',
		'sections' => ['submissions' => [
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
		]]
	];
};
