<?php

return function () {
	return [
		'label' => t('dreamform.field'),
		'type' => 'select',
		'options' => [
			'type' => 'query',
			'query' => 'page.fieldOptions'
		],
	];
};
