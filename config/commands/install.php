<?php

use Kirby\CLI\CLI;
use Kirby\Cms\App;

return [
	'description' => 'Install DreamForm & create the necessary pages',
	'args' => [],
	'command' => static function (CLI $cli): void {
		$app = App::instance();
		$cli->info('Scaffolding DreamForm pages...');
		$page = $app->option('tobimori.dreamform.page');
		if ($page !== 'page://forms') {
			$cli->error('The DreamForm page is set to a non-standard location. Please create the page manually.');
			return;
		}

		if ($app->page('page://forms')) {
			$cli->info('DreamForm already installed.');
			return;
		}

		$app->impersonate(
			'kirby',
			fn () =>
			$app->site()->createChild([
				'slug' => 'forms',
				'template' => 'forms',
				'model' => 'forms',
				'content' => [
					'Uuid' => 'forms'
				]
			])->changeStatus('unlisted')
		);

		$cli->success('DreamForm installed successfully.');
	}
];
