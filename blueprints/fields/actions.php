<?php

return function () {
	$actions = option('tobimori.dreamform.actions', []);
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
