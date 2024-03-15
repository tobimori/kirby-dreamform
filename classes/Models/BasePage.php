<?php

namespace tobimori\DreamForm\Models;

use Kirby\Cms\Page;
use Kirby\Content\Field;

class BasePage extends Page
{
	/**
	 * Render a 404 page to lock pages
	 */
	public function render(array $data = [], $contentType = 'html'): string
	{
		kirby()->response()->code(404);
		return $this->site()->errorPage()->render();
	}

	/**
	 * Override the page title to be static to the template name
	 */
	public function title(): Field
	{
		return new Field($this, 'title', t("dreamform.{$this->intendedTemplate()->name()}"));
	}
}
