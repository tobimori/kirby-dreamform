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
use tobimori\DreamForm\DreamForm;
use tobimori\DreamForm\Exceptions\PerformerException;
use tobimori\DreamForm\Exceptions\SilentPerformerException;

class FormPage extends BasePage
{
	private array $fields;
	private array $steps;

	/**
	 * Returns the title field or the slug as fallback
	 */
	public function title(): Field
	{
		return $this->content()->get('title')->or($this->slug());
	}

	/**
	 * Returns the field layouts for the given step
	 */
	public function layouts(int $step = 1): Layouts
	{
		if (count($this->steps()) < $step) {
			throw new Exception("Step {$step} does not exist");
		}

		return $this->steps()[$step - 1];
	}

	public function currentLayouts(): Layouts
	{
		$submission = SubmissionPage::fromSession();
		if ($submission) {
			return $this->layouts($submission->currentStep());
		}

		return $this->layouts();
	}

	/**
	 * Returns an array of steps for a multi-step form
	 * This is an array, because Kirby's collection class does not allow
	 * for empty IDs and Layouts instances can't have an ID
	 */
	public function steps(): array
	{
		if (isset($this->steps)) {
			return $this->steps;
		}

		$steps = [];
		$step = Layouts::factory([], ['parent' => $this]);

		foreach ($this->content()->get('fields')->toLayouts() as $layout) {
			if ($layout->columns()->first()->width() === 'dreamform-page') {
				$steps[] = $step;
				$step = Layouts::factory([], ['parent' => $this]);
			} else {
				$step->append($layout);
			}
		}

		$steps[] = $step;
		return $this->steps = $steps;
	}

	/**
	 * Returns true if the form is a multi-step form
	 */
	public function isMultiStep(): bool
	{
		return count($this->steps()) > 1;
	}

	/**
	 * Returns the fields for a form
	 */
	public function fields(int $step = null): Collection
	{
		if (isset($step) && ($step < 1 || $step > count($this->steps()))) {
			throw new Exception("Step {$step} does not exist");
		}

		if (isset($this->fields)) {
			return new Collection($step ? $this->fields[$step - 1] : $this->fields);
		}

		$steps = [];
		foreach ($this->steps() as $stepLayout) {
			$fields = [];
			foreach ($stepLayout->toBlocks() as $block) {
				$type = Str::replace($block->type(), '-field', '');

				$field = DreamForm::field($type, $block);
				if ($field) {
					$fields[] = $field;
				}
			}

			$steps[] = new Collection($fields, []);
		}

		$this->fields = $steps;
		return new Collection($step ? $steps[$step - 1] : $steps);
	}

	private array $guards;

	/**
	 * Returns all guards for the form
	 */
	public function guards(): array
	{
		if (isset($this->guards)) {
			return $this->guards;
		}

		$availableGuards = DreamForm::guards();
		$guards = [];

		foreach ($availableGuards as $guard) {
			$guards[] = new $guard($this);
		}

		return $this->guards = $guards;
	}

	/**
	 * Create a new (virtual) submission page
	 */
	public function initSubmission(): SubmissionPage
	{
		$request = App::instance()->request();

		$referer = null;
		$url = $request->header("Referer");
		if (isset($url)) {
			$site = Url::toObject($this->site()->url());
			$path = Url::toObject($url);

			// if the referer is from the same site, we can assume
			// a "safe" PRG redirect
			if ($site->host() === $path->host()) {
				$referer = $path->path();
			}
		}

		return new SubmissionPage([
			'template' => 'submission',
			'slug' => $uuid = Uuid::generate(),
			'parent' => $this,
			'content' => [
				'dreamform_submitted' => date('c'),
				'dreamform_referer' => $referer,
				'dreamform_state' => [
					'success' => true,
					'partial' => true,
					'step' => 1,
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
		// create a new submission or get the existing one from the session
		$submission = SubmissionPage::fromSession() ?? $this->initSubmission();
		// if the submission is from a different form, create a new one
		if ($submission->parent()->id() !== $this->id()) {
			$submission = $this->initSubmission();
		}

		// handle guards (honeypot, csrf, etc.)
		try {
			foreach ($this->guards() as $guard) {
				$guard->run();
			}
		} catch (Exception $e) {
			// if an guard fails, set a common error and stop the form submission
			if ($e instanceof PerformerException) {
				$submission->setError($e->getMessage());

				// if the exception is silent, stop the form submission early as "successful"
			} elseif ($e instanceof SilentPerformerException) {
				return $submission->storeSession()->finish(false);
			}
		}

		// handle fields
		$currentStep = App::instance()->request()->query()->get('dreamform-step', 1);
		foreach ($this->fields($currentStep) as $field) {
			// skip "decorative" fields that don't have a value
			if (!$field::hasValue()) {
				continue;
			}

			// create a field instance & set the value from the request
			$field = $submission->updateFieldFromRequest($field);

			// validate the field
			$validation = $field->validate();

			if ($validation !== true) {
				// if the validation fails, set an error in the submission state
				$submission->setError(field: $field->key(), message: $validation);
			} else {
				// otherwise add it to the content of the submission
				$submission->setField($field);
			}
		}

		// run actions if the field validations where successful and the form is complete
		$isFinalStep = !$this->isMultiStep() || $submission->currentStep() === count($this->steps());
		if ($isFinalStep && $submission->isSuccessful()) {
			try {
				foreach ($submission->createActions() as $action) {
					// TODO: log data for action log?
					$action->run();
				}
			} catch (Exception $e) {
				// if an action fails, set a common error and stop the form submission
				if ($e instanceof PerformerException) {
					$submission->setError($e->getMessage());
					// if the exception is silent, stop the form submission as "successful"
				} elseif ($e instanceof SilentPerformerException) {
					return $submission->storeSession()->finish(false);
				}
			}
		}

		// finish the submission or advance to the next step for multi-step forms
		if ($submission->isSuccessful()) {
			if ($isFinalStep) {
				$submission->finish();
			} else {
				$submission->advanceStep();
			}
		}

		// store the submission in the session
		$submission->storeSession();

		return $submission;
	}

	/**
	 * Runs the form handling, or renders a 404 page
	 */
	public function render(array $data = [], $contentType = 'html'): string
	{
		$kirby = App::instance();

		if ($kirby->request()->method() === 'POST') {
			$submission = $this->submit();

			// if dreamform is used in API mode, return the submission state as JSON
			if (App::instance()->option('tobimori.dreamform.mode', 'prg') === 'api') {
				$kirby->response()->code($submission->isSuccessful() ? 200 : 400);
				return Json::encode($submission->state()->toArray());
			}

			if (!$submission->isSuccessful()) {
				return $submission->redirectToReferer();
			}

			// otherwise, redirect to origin page (referer header)
			return $submission->redirect();
		}

		$kirby->response()->code(404);
		return $this->site()->errorPage()->render();
	}

	/**
	 * Returns the URL for the form
	 */
	public function url($options = null): string
	{
		$url = parent::url($options);
		if ($this->isMultiStep()) {
			$submission = SubmissionPage::fromSession();
			if ($submission) {
				$url .= "?dreamform-step={$submission->currentStep()}";
			} else {
				$url .= "?dreamform-step=1";
			}
		}

		return $url;
	}

	/**
	 * Saves the form and checks for duplicate keys
	 */
	public function save(?array $data = null, ?string $languageCode = null, bool $overwrite = false): static
	{
		$page = clone $this; // clone the page to avoid side effects
		unset($page->steps, $page->fields); // reset layout calculations cache
		$page->content = $page->content($languageCode)->update($data); // update the content

		// check for duplicate keys
		$keys = [];
		foreach ($page->fields() as $field) {
			$key = $field->key();
			if (in_array($key, $keys)) {
				throw new Exception(tt('dreamform.duplicate-key', ['key' => $key]));
			}

			if (Str::startsWith($key, 'dreamform')) {
				throw new Exception(tt('dreamform.reserved-key', ['key' => $key]));
			}

			$keys[] = $key;
		}

		return parent::save($data, $languageCode, $overwrite);
	}

	/**
	 * Never cache the API response
	 */
	public function isCacheable(): bool
	{
		return false;
	}

	/**
	 * Returns the value for a given field key from the submission or URL params
	 */
	public function valueFor(string $key): Field|null
	{
		$submission = SubmissionPage::fromSession();
		if (!$submission) {
			return SubmissionPage::valueFromQuery($key);
		}

		return $submission->valueFor($key);
	}

	/**
	 * Returns the error message for a given field key
	 */
	public function errorFor(string $key): string|null
	{
		return SubmissionPage::fromSession()?->errorFor($key);
	}

	/**
	 * Static function to get page fields based on the API request url for use in panel blueprints
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
