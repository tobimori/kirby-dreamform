<?php

@include_once __DIR__ . '/vendor/autoload.php';

use Kirby\Cms\App;

if (
	version_compare(App::version() ?? '0.0.0', '4.0.0-beta.1', '<') === true ||
	version_compare(App::version() ?? '0.0.0', '5.0.0', '>') === true
) {
	throw new Exception('Kirby Dream Form requires Kirby 4');
}

App::plugin('tobimori/kirby-dreamform', [
	'pageModels' => [
		'forms' => 'tobimori\DreamForm\Models\FormsPage',
		'form' => 'tobimori\DreamForm\Models\FormPage',
		'submission' => 'tobimori\DreamForm\Models\SubmissionPage',
	],
	'blueprints' => [
		'pages/forms' => __DIR__ . '/blueprints/pages/forms.yml',
		'pages/form' => __DIR__ . '/blueprints/pages/form.yml',
		'pages/submission' => __DIR__ . '/blueprints/pages/submission.yml',
	],
]);
