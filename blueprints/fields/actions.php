<?php

use tobimori\DreamForm\DreamForm;

return function () {
	$fieldsets = [];

	foreach (DreamForm::actions() as $type => $action) {
		if (!isset($fieldsets[$group = $action::group()])) {
			$fieldsets[$group] = [
				'label' => t("dreamform.actions.category.{$group}"),
				'type' => 'group',
				'fieldsets' => []
			];
		}

		$fieldsets[$group]['fieldsets']["{$type}-action"] = $action::blueprint();
	}

	return [
		'label' => t('dreamform.actions.label'),
		'type' => 'blocks',
		'empty' => t('dreamform.actions.empty'),
		'fieldsets' => $fieldsets
	];
};
