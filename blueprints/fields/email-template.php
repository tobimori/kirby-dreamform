<?php

use Kirby\Filesystem\Dir;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;

return function () {
	$templates = Dir::read(kirby()->root('templates') . '/emails');
	$templates = array_unique(A::map($templates, fn ($name) => Str::split($name, '.')[0]));

	// build options array with dreamform template first
	$options = [
		'dreamform' => t('dreamform.actions.email.templateType.default')
	];

	// add other templates
	foreach ($templates as $template) {
		if ($template !== 'dreamform') {
			$options[$template] = $template;
		}
	}

	return [
		'label' => t('template'),
		'type' => 'select',
		'default' => 'dreamform',
		'options' => $options,
	];
};
