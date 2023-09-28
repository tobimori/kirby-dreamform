<?php

namespace tobimori\DreamForm\Models;

use Kirby\Cms\Page;

class SubmissionPage extends Page
{
	public function render(array $data = [], $contentType = 'html'): string
	{
		return $this->site()->errorPage()->render();
	}
}
