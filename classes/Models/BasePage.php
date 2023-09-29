<?php

namespace tobimori\DreamForm\Models;

use Kirby\Cms\Page;

class BasePage extends Page
{
	/** Render a 404 page to lock pages */
	public function render(array $data = [], $contentType = 'html'): string
	{
		kirby()->response()->code(404);
		return $this->site()->errorPage()->render();
	}
}
