<?php

namespace tobimori\DreamForm\Actions;

use tobimori\DreamForm\Models\SubmissionPage;

class EmailAction extends Action
{
	public static function blueprint(): array
	{
		return [
			'title' => t('send-email-action'),
			'preview' => 'fields',
			'wysiwyg' => true,
			'icon' => 'email',
			'tabs' => []
		];
	}

	public function __construct(SubmissionPage $submission)
	{
	}

	public function run(): void
	{
	}
}
