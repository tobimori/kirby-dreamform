<?php

namespace tobimori\DreamForm\Models;

use Kirby\Cms\Collection;
use Kirby\Cms\Layouts;
use Kirby\Content\Field;
use Kirby\Data\Json;
use Kirby\Http\Request;
use Kirby\Toolkit\Str;

class FormPage extends BasePage
{
	private Collection $fields;

	public function __construct(array $props)
	{
		parent::__construct($props);

		$active = option('tobimori.dreamform.fields', []);
		$fields = [];

		foreach ($this->fieldLayouts() as $layout) {
			foreach ($layout->columns() as $column) {
				foreach ($column->blocks() as $block) {
					$type = Str::replace($block->type(), '-field', '');

					if (!key_exists($type, $active)) {
						continue;
					}

					$fields[] = new $active[$type]($block);
				}
			}
		}

		$this->fields = new Collection($fields, []);
	}

	public static function getFields(Request $request): array
	{
		$path = $request->path()->data()[2];
		$page = page(Str::replace($path, '+', '/'));

		$fields = [];

		foreach ($page->fields() as $field) {
			$fields[] = [
				'label' => t($field->field()->type()) . ($field->field()->label()->isNotEmpty() ? ": {$field->field()->label()}" : ""),
				'id' => $field->field()->id(),
			];
		}

		return $fields;
	}

	public function fieldLayouts(): Layouts
	{
		return $this->content()->get('fields')->toLayouts();
	}

	public function fields(): Collection
	{
		return $this->fields;
	}

	/**
	 * Main form handler
	 */
	public function run(): array|null
	{
		$request = kirby()->request();
		$data = null;

		foreach ($this->fields() as $field) {
			$body = $request->body()->get($field->field()->id()) ?? null;
			$field->setContent(new Field($this, $field->field()->id(), $body));

			$validation = $field->validate();

			if ($validation !== true) {
				$data ??= [];
				$data[$field->field()->id()] = $validation;
			}
		}

		foreach ($this->actions() as $action) {
		}

		return $data;
	}

	public function render(array $data = [], $contentType = 'html'): string
	{
		$kirby = kirby();

		if ($kirby->request()->method() === 'POST') {
			$errors = $this->run();

			if ($errors === null) {
				$kirby->response()->code(200);
				return Json::encode(['success' => true]);
			}

			$kirby->response()->code(400);
			return Json::encode(['success' => false, ...$errors]);
		}

		$kirby->response()->code(404);
		return $this->site()->errorPage()->render();
	}
}
