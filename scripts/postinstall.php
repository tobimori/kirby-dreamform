<?php

use Kirby\Cms\App;

require dirname(__DIR__, 4) . '/kirby/bootstrap.php';

$app = new App();

if ($app->page('page://forms')) {
	echo 'DreamForm already installed.';
	return;
}

$app->impersonate('kirby');
$app->site()->createChild([
	'slug' => 'forms',
	'template' => 'forms',
	'model' => 'forms',
	'content' => [
		'Uuid' => 'forms'
	]
])->changeStatus('unlisted');
$app->impersonate();

echo 'DreamForm installed successfully.';
