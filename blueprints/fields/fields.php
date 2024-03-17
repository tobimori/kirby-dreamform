<?php

use tobimori\DreamForm\Models\FormPage;

return function () {
	$layouts = option('tobimori.dreamform.layouts', ['1/1']);
	$fieldsets = [];

	$active = option('tobimori.dreamform.fields', true);
	$registered = FormPage::$registeredFields;
	foreach ($registered as $type => $field) {
		if (is_array($active) ? !in_array($type, $active) : $active !== true) {
			continue;
		}

		if (!isset($fieldsets[$group = $field::group()])) {
			$fieldsets[$group] = [
				'label' => t("dreamform.{$group}"),
				'type' => 'group',
				'fieldsets' => []
			];
		}

		$fieldsets[$group]['fieldsets']["{$type}-field"] = $field::blueprint();
	}

	return [
		'label' => t('dreamform.fields'),
		'type' => 'layout',
		'layouts' => ["dreamform-page", ...$layouts],
		'fieldsets' => $fieldsets
	];
};
