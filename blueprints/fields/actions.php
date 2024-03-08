<?php

use tobimori\DreamForm\Models\FormPage;

return function () {
	$fieldsets = [];

	$active = option('tobimori.dreamform.actions', true);
	$registered = FormPage::$registeredActions;
	foreach ($registered as $type => $action) {
		if (is_array($active) ? !in_array($type, $active) : $active !== true) {
			continue;
		}

		$fieldsets["{$type}-action"] = $action::blueprint();
	}

	return [
		'label' => t('dreamform.actions'),
		'type' => 'blocks',
		'empty' => t('dreamform.empty-actions'),
		'fieldsets' => $fieldsets
	];
};
