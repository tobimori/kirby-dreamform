<?php

use tobimori\DreamForm\Models\SubmissionPage;

return function () {
	$layouts = option('tobimori.dreamform.layouts', ['1/1']);
	$fieldsets = [];

	$active = option('tobimori.dreamform.fields', true);
	$registered = SubmissionPage::$registeredFields;
	foreach ($registered as $type => $field) {
		if (is_array($active) ? !in_array($type, $active) : $active !== true) {
			continue;
		}

		$fieldsets["{$type}-field"] = $field::blueprint();
	}

	return [
		'label' => t('fields'),
		'type' => 'layout',
		'layouts' => $layouts,
		'fieldsets' => $fieldsets
	];
};
