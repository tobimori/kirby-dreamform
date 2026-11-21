<?php

use Kirby\Cms\App;
use tobimori\DreamForm\DreamForm;

return function () {
	$layouts = DreamForm::option('layouts', ['1/1']);
	$fieldsets = [];

	foreach (DreamForm::fields() as $type => $field) {
		if (!isset($fieldsets[$group = $field::group()])) {
			$fieldsets[$group] = [
				'label' => t("dreamform.fields.category.{$group}"),
				'type' => 'group',
				'fieldsets' => []
			];
		}

		$fieldsets[$group]['fieldsets']["{$type}-field"] = $field::blueprint();
	}

	$multiStep = DreamForm::option('multiStep', true);

	return [
		'label' => t('dreamform.fields'),
		'type' => 'layout',
		'layouts' => $multiStep ? ["dreamform-page", ...$layouts] : $layouts,
		'fieldsets' => $fieldsets,
		'agents' => [
			'as' => 'dreamform-fields',
			'description' => $multiStep
				? 'A row with the single column `dreamform-page` and no blocks starts the next step of the form, the rows after it are the fields of that step. Each step needs a button-field to submit it'
				: 'The form needs a button-field to submit it'
		]
	];
};
