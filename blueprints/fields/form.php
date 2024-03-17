<?php

use Kirby\Cms\App;

return function () {
	$page = App::instance()->option('tobimori.dreamform.page', 'page://forms');

	return [
		'label' => t('dreamform.form'),
		'type' => 'pages',
		'query' => "page('{$page}').children.listed.filterBy('intendedTemplate', 'form')",
		'empty' => t('dreamform.empty-form'),
		'multiple' => false
	];
};
