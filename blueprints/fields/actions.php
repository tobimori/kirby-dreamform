<?php

use Kirby\Cms\App;
use tobimori\DreamForm\Models\FormPage;

return function (App $kirby) {
	$fields = FormPage::getFields($kirby->request());
	$actions  = option('tobimori.dreamform.actions', []);
	$fieldsets = [];

	foreach ($actions as $type => $action) {
		$fieldsets["{$type}-action"] = $action::blueprint();
	}

	return [
		'label' => t('actions'),
		'type' => 'blocks',
		'empty' => t('empty-actions'),
		'fieldsets' => $fieldsets
	];
};
