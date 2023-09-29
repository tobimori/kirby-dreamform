<?php

namespace tobimori\DreamForm\Models;

use Kirby\Cms\Page;

class BasePage extends Page
{
	public function render(array $data = [], $contentType = 'html'): string
	{
		kirby()->response()->code(404);
		return $this->site()->errorPage()->render();
	}
}
