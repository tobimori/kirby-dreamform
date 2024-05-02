<?php

use Kirby\Cms\App;

return function () {
	if (!App::instance()->user()->role()->permissions()->for('tobimori.dreamform', 'accessForms')) {
		return [
			'type' => 'hidden',
		];
	}

	$page = App::instance()->option('tobimori.dreamform.page', 'page://forms');

	return [
		'label' => t('dreamform.form'),
		'type' => 'pages',
		'query' => "page('{$page}').children.listed.filterBy('intendedTemplate', 'form')",
		'empty' => t('dreamform.form.empty'),
		'multiple' => false
	];
};
