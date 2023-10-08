<?php

return function () {
	$page = option('tobimori.dreamform.page', 'page://forms');

	return [
		'label' => t('form'),
		'type' => 'pages',
		'query' => "page('{$page}').children.listed.filterBy('intendedTemplate', 'form')",
		'multiple' => false
	];
};
