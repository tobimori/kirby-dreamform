<?php

return function () {
	$layouts = option('tobimori.dreamform.layouts', ['1/1']);
	$fields = option('tobimori.dreamform.fields', []);
	$fieldsets = [];

	foreach ($fields as $type => $field) {
		$fieldsets["{$type}-field"] = $field::blueprint();
	}

	return [
		'label' => t('fields'),
		'type' => 'layout',
		'layouts' => $layouts,
		'fieldsets' => $fieldsets
	];
};
