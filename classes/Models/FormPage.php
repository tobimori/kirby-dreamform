<?php

namespace tobimori\DreamForm\Models;

use Exception;
use Kirby\Cms\App;
use Kirby\Cms\Collection;
use Kirby\Cms\Layouts;
use Kirby\Content\Field;
use Kirby\Data\Json;
use Kirby\Http\Url;
use Kirby\Toolkit\Str;
use Kirby\Uuid\Uuid;
use Kirby\Toolkit\A;
use Random\RandomException;
use tobimori\DreamForm\Actions\Action;
use tobimori\DreamForm\DreamForm;
use tobimori\DreamForm\Exceptions\SuccessException;

class FormPage extends BasePage
{
	public static $registeredFields = [];
	public static $registeredActions = [];
	private Collection $fields;

	/**
	 * Returns the title field or the slug as fallback
	 */
	public function title(): Field
	{
		return $this->content()->get('title')->or($this->slug());
	}

	/**
	 * Create a form page, resolve the stored field layout to a flat collection
	 */
	public function __construct(array $props)
	{
		parent::__construct($props);

		$fields = [];

		$active = option('tobimori.dreamform.fields', true);
		$registered = static::$registeredFields;

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

	/**
	 * Create a new (virtual) submission page
	 */
	public function initSubmission(): SubmissionPage
	{
		$request = App::instance()->request();

		// try to get page from referer header
		$referer = null;
		if (isset($request->headers()["Referer"])) {
			$url = $request->headers()["Referer"];
			$path = Url::path($url);
			$referer = App::instance()->site()->findPageOrDraft($path);
		}

		return new SubmissionPage([
			'template' => 'submission',
			'slug' => $uuid = Uuid::generate(),
			'parent' => $this,
			'content' => [
				'dreamform-submitted' => date('c'),
				'dreamform-referer' => $referer,
				'dreamform-values' => [], // this will store the validated and sanitized values
				'dreamform-state' => [
					'success' => true,
					'redirect' => null, // this is the redirect URL if the form was successful
					'error' => null, // this is a common error message for the whole form
					'errors' => [], // this is an array of field-specific error messages
				],
				'uuid' => $uuid,
			]
		]);
	}

	/**
	 * Main form handler
	 */
	public function submit(): SubmissionPage
	{
		// create a new submission
		$submission = $this->initSubmission();

		// TODO: handle guards like honeypot upfront

		// handle fields
		foreach ($this->fields() as $field) {
			// create a field instance & set the value from the request
			$submission->createFieldFromRequest($field);

			// validate the field
			$validation = $field->validate();

			if (!$validation) {
				// if the validation fails, set an error in the submission state
				$submission->setError(field: $field->key(), message: $validation);
			} else {
				// otherwise add it to the content of the submission
				$submission->setField($field);
			}
		}

		// only run actions if the field validations where successful
		if ($submission->isSuccessful()) {
			try {
				foreach ($submission->createActions() as $action) {
					$action->run();
				}
			} catch (Exception $e) {
				if (!($e instanceof SuccessException)) {
					$submission->setError($e->getMessage());
				}
			}
		}

		$submission->finish();
		return $submission;
	}

	/** Runs the form handling, or renders a 404 page */
	public function render(array $data = [], $contentType = 'html'): string
	{
		$kirby = App::instance();

		if ($kirby->request()->method() === 'POST') {
			$submission = $this->submit();

			// Content-Type is application/json, the request has to be sent manually, so we send JSON data back
			if ($kirby->request()->header('Content-Type') === 'application/json') {
				$kirby->response()->code($submission->isSuccessful() ? 200 : 400);
				return Json::encode($submission->state()->toArray());
			}

			// otherwise, redirect to origin page (referer header)
			return $submission->redirect();
		}

		$kirby->response()->code(404);
		return $this->site()->errorPage()->render();
	}

	/**
	 * Static function to get page fields based on
	 * the API request url for use in panel blueprints
	 */
	public static function getFields(): array
	{
		$page = DreamForm::currentPage();
		if (!$page) {
			return [];
		}

		$fields = [];
		foreach ($page->fields() as $field) {
			$type = Str::replace($field->block()->type(), '-field', '');
			$fields[$field->id()] = "{$field->block()->label()->or($field->key())} ({$type})";
		}

		return $fields;
	}
}
