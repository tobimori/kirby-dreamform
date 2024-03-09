<?php

use tobimori\DreamForm\Models\FormPage;

return function () {
	$fields = FormPage::getFields();

	return [
		'label' => t('dreamform.field'),
		'type' => 'select',
		'options' => $fields,
	];
};
