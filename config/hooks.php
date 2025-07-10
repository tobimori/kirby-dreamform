<?php

use tobimori\DreamForm\DreamForm;
use tobimori\DreamForm\Models\SubmissionPage;
use tobimori\DreamForm\Storage\SubmissionSessionStorage;

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

	/**
	 * Clean up empty submissions with errors after rendering (flash behavior)
	 */
	'page.render:after' => function (string $contentType, array $data, string $html, Kirby\Cms\Page $page) {
		$submission = $data['submission'] ?? null;

		// if submission exists, is empty clean it up after render
		if (
			$submission instanceof SubmissionPage &&
			$submission->isEmpty()
		) {
			$storage = $submission->storage();

			if (method_exists($storage, 'cleanup')) {
				/** @var SubmissionSessionStorage $storage */
				$storage->cleanup();
			}
		}
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
