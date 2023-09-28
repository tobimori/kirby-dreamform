<?php

use Kirby\Cms\App;

return function (App $kirby) {
	$layouts = option('tobimori.dreamform.layouts', ['1/1']);
	$fields = option('tobimori.dreamform.fields', []);
	$fieldsets = [];

	foreach ($fields as $field) {
		$fieldsets["{$field::$type}-field"] = $field::blueprint();
	}

	return [
		'label' => t('fields'),
		'type' => 'layout',
		'layouts' => $layouts,
		'fieldsets' => $fieldsets
	];
};
