<?php

namespace tobimori\DreamForm\Panel\Controller\Dialog;

use Kirby\Cms\Find;
use Kirby\Exception\NotFoundException;
use Kirby\Panel\Controller\DialogController;
use tobimori\DreamForm\Models\SubmissionPage;

/**
 * Controls a Panel dialog for a specific submission
 */
abstract class SubmissionDialogController extends DialogController
{
	public function __construct(
		public SubmissionPage $submission
	) {
		parent::__construct();
	}

	/**
	 * @throws NotFoundException If the page is not a submission or the user cannot access it
	 */
	public static function factory(string $id): static
	{
		$submission = Find::page($id);

		if (!($submission instanceof SubmissionPage)) {
			throw new NotFoundException(
				key: 'page.notFound',
				data: ['slug' => $id]
			);
		}

		return new static(submission: $submission);
	}
}
