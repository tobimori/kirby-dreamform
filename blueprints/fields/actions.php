<?php

use tobimori\DreamForm\DreamForm;

return function () {
	$fieldsets = [];

	foreach (DreamForm::actions() as $type => $action) {
		if (!isset($fieldsets[$group = $action::group()])) {
			$fieldsets[$group] = [
				'label' => t("dreamform.{$group}-actions"),
				'type' => 'group',
				'fieldsets' => []
			];
		}

		$fieldsets[$group]['fieldsets']["{$type}-action"] = $action::blueprint();
	}

	return [
		'label' => t('dreamform.actions'),
		'type' => 'blocks',
		'empty' => t('dreamform.empty-actions'),
		'fieldsets' => $fieldsets
	];
};
