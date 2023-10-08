<?php

namespace tobimori\DreamForm\Models;

use Kirby\Cms\Collection;
use Kirby\Cms\Layouts;
use Kirby\Content\Field;
use Kirby\Data\Json;
use Kirby\Http\Request;
use Kirby\Http\Url;
use Kirby\Toolkit\Str;
use Kirby\Uuid\Uuid;

class FormPage extends BasePage
{
	private Collection $fields;

	/** Create a form page & Form Field objects */
	public function __construct(array $props)
	{
		parent::__construct($props);

		$fields = [];

		$active = option('tobimori.dreamform.fields', true);
		$registered = SubmissionPage::$registeredFields;

		foreach ($this->fieldLayouts() as $layout) {
			foreach ($layout->columns() as $column) {
				foreach ($column->blocks() as $block) {
					$type = Str::replace($block->type(), '-field', '');

					if (!key_exists($type, $registered)) {
						continue;
					}

					if (is_array($active) && !in_array($type, $active) || $active != true) {
						continue;
					}

					$fields[] = new $registered[$type]($block);
				}
			}
		}

		$this->fields = new Collection($fields, []);
	}

	/** Returns the form layouts */
	public function fieldLayouts(): Layouts
	{
		return $this->content()->get('fields')->toLayouts();
	}

	/** Returns the fields for a form  */
	public function fields(): Collection
	{
		return $this->fields;
	}

	/** Create actions for a submission */
	public function actions(SubmissionPage $submission): Collection
	{
		$active = option('tobimori.dreamform.actions', true);
		$registered = SubmissionPage::$registeredActions;
		$actions = [];

		foreach ($this->content()->get('actions')->toBlocks() as $block) {
			$type = Str::replace($block->type(), '-action', '');

			if (!key_exists($type, $registered)) {
				continue;
			}

			if (is_array($active) && !in_array($type, $active) || $active != true) {
				continue;
			}

			$actions[] = new $registered[$type]($block, $this, $submission);
		}

		return new Collection($actions, []);
	}

	/** Main form handler */
	public function run(): array|null
	{
		$request = kirby()->request();
		$data = null;

		foreach ($this->fields() as $field) {
			$key = $field->field()->key()->or($field->field()->id())->value();
			$body = $request->body()->get($key) ?? null;
			$field->setValue(new Field($this, $key, $body));

			$validation = $field->validate();

			if ($validation !== true) {
				$data ??= [];
				$data[$key] = $validation;
			}
		}

		if ($data !== null) {
			return $data;
		}

		$referer = null;
		// try to get page from referer header
		if (isset($request->headers()["Referer"])) {
			$url = $request->headers()["Referer"];
			$path = Url::path($url);
			$referer = page($path);
		}

		// this is just virtual for now and won't be stored
		$submission = new SubmissionPage([
			'slug' => $uuid = Uuid::generate(),
			'fields' => $this->fields(),
			'referer' => $referer,
			'parent' => $this,
			'content' => [
				'uuid' => $uuid
			]
		]);

		try {
			foreach ($this->actions($submission) as $action) {
				$action->run();
			}
		} catch (\Exception $e) {
			$data ??= [];
			$data['error'] = $e->getMessage();
		}

		return $data;
	}

	/** Runs the form handling, or renders a 404 page */
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

	/**
	 * Static function to get page fields based on
	 * the API request url for use in panel blueprints
	 */
	public static function getFields(Request $request): array
	{
		$path = $request->path()->data()[2];
		$page = page(Str::replace($path, '+', '/'));

		$fields = [];
		foreach ($page->fields() as $field) {
			$type = Str::replace($field->field()->type(), '-field', '');
			$fields[$field->id()] = "{$field->field()->label()->or($field->field()->key())->value()} ({$type})";
		}

		return $fields;
	}
}
