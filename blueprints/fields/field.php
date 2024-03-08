<?php

use tobimori\DreamForm\Models\FormPage;

return function () {
	$fields = FormPage::getFields(kirby()->request());

	return [
		'label' => t('dreamform.field'),
		'type' => 'select',
		'options' => $fields,
	];
};
