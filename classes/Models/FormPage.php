<?php

namespace tobimori\DreamForm\Models;

use Kirby\Cms\Blocks;
use Kirby\Cms\Layout;
use Kirby\Cms\Page;
use Kirby\Http\Request;
use Kirby\Toolkit\Str;

class FormPage extends Page
{
	public static function getFields(Request $request): array
	{
		$path = $request->path()->data()[2];
		$page = page(Str::replace($path, '+', '/'));

		$fields = [];

		foreach ($page->fields() as $block) {
			$fields[] = [
				'label' => t($block->type()) . ($block->label()->isNotEmpty() ?  ": {$block->label()}" : ""),
				'id' => $block->id(),
			];
		}

		return $fields;
	}

	public function fieldLayout(): Layout
	{
		return $this->content()->get('fields')->toLayouts();
	}

	/** Flatten layout */
	public function fields(): Blocks
	{
		$blocks = [];

		foreach ($this->fieldLayout() as $layout) {
			foreach ($layout->columns() as $column) {
				foreach ($column->blocks() as $block) {
					$blocks[] = $block;
				}
			}
		}

		return new Blocks($blocks, []);
	}

	public function render(array $data = [], $contentType = 'html'): string
	{
		return $this->site()->errorPage()->render();
	}
}
