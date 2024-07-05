<?php

use tobimori\DreamForm\DreamForm;
use tobimori\DreamForm\Models\SubmissionPage;

return [
	/**
	 * Create form page if it doesn't exist yet
	 */
	'system.loadPlugins:after' => function () {
		DreamForm::install();
	},

	/**
	 * Injects submission variables in the page rendering process
	 */
	'page.render:before' => function (string $contentType, array $data, Kirby\Cms\Page $page) {
		return [
			...$data,
			'submission' => SubmissionPage::fromSession()
		];
	},

	/*
	 * Deletes all files associated with a submission page with elevated permissions,
	 * so we can disallow deleting single files from the panel
	 */
	'page.delete:before' => function (Kirby\Cms\Page $page) {
		if ($page->intendedTemplate()->name() === 'submission') {
			$page->kirby()->impersonate('kirby');
			foreach ($page->files() as $file) {
				$file->delete();
			}
			$page->kirby()->impersonate();
		}
	}
];
