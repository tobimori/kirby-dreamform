<?php

namespace tobimori\DreamForm\Models;

use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\Content\Field;

/**
 * The base page class for all pages in the plugin.
 */
class BasePage extends Page
{
	/**
	 * Disable sitemap for all pages
	 * Integration into tobimori/kirby-seo
	 */
	public function metaDefaults()
	{
		return ['robotsIndex' => false];
	}

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

	/**
	 * Basic permissions for all pages
	 */
	public function isAccessible(): bool
	{
		if (!App::instance()->user()->role()->permissions()->for('tobimori.dreamform', 'accessForms')) {
			return false;
		}

		return parent::isAccessible();
	}
}
