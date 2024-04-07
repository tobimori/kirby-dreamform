<?php

namespace tobimori\DreamForm\Models;

use Exception;
use Kirby\Cms\App;
use Kirby\Cms\Collection;
use Kirby\Cms\Layouts;
use Kirby\Cms\Page;
use Kirby\Content\Field;
use Kirby\Data\Json;
use Kirby\Http\Response;
use Kirby\Http\Url;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;
use Kirby\Uuid\Uuid;
use tobimori\DreamForm\DreamForm;
use tobimori\DreamForm\Exceptions\PerformerException;
use tobimori\DreamForm\Exceptions\SilentPerformerException;
use tobimori\DreamForm\Guards\LicenseGuard;
use tobimori\DreamForm\Permissions\FormPermissions;
use tobimori\DreamForm\Support\Htmx;

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

	public function htmxAttr(Page $page, array $attr, SubmissionPage|null $submission): array
	{
		if (!Htmx::isActive()) {
			return [];
		}

		$htmx = [
			'hx-post' => $this->url(),
			'hx-swap' => 'outerHTML',
			'hx-vals' => Json::encode(array_filter([
				'dreamform:session' => $submission && Htmx::isHtmxRequest() ? Htmx::encrypt($submission->uuid()->toString()) : null,
				'dreamform:page' => Htmx::encrypt($page->uuid()->toString()),
				'dreamform:attr' => Htmx::encrypt(Json::encode($attr))
			], fn ($value) => $value !== null))
		];

		return $htmx;
	}

	public function attr(): array
	{
		$attr = [
			'enctype' => $this->enctype(),
			'method' => 'POST',
			'novalidate' => 'novalidate',
			(App::instance()->option('tobimori.dreamform.useDataAttributes') ? 'data-form-url' : 'action') => $this->url()
		];

		return $attr;
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

				$field = DreamForm::field($type, $block, $this);
				if ($field) {
					$fields[] = $field;
				}
			}

			$steps[] = new Collection($fields);
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

		$guards[] = new LicenseGuard($this);
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
					'spam' => false,
					'step' => 1,
					'redirect' => null, // this is a redirect URL for the form
					'error' => null, // this is a common error message for the whole form
					'errors' => [], // this is an array of field-specific error messages
					'actions' => false // this is an array of action data or false if no actions have been run
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

		$submission = App::instance()->apply(
			'dreamform.submit:before',
			['submission' => $submission, 'form' => $this],
			'submission'
		);

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
		foreach ($fields = $this->fields($currentStep) as $field) {
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
				$submission->removeError($field->key());
			}
		}

		// run actions if the field validations where successful and the form is complete
		$isFinalStep = !$this->isMultiStep() || $submission->currentStep() === count($this->steps());
		if ($isFinalStep && $submission->isSuccessful()) {
			try {
				foreach ($submission->createActions() as $action) {
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

			// run the afterSubmit method for all fields
			// this can be used to store files or do other post-processing
			foreach ($fields as $field) {
				$field->afterSubmit($submission);
			}
		}

		// store the submission in the session
		return $submission->storeSession();
	}

	/**
	 * Runs the form handling, or renders a 404 page
	 */
	public function render(array $data = [], $contentType = 'html'): string
	{
		$kirby = App::instance();
		$mode = $kirby->option('tobimori.dreamform.mode', 'prg');

		if ($kirby->request()->method() === 'POST') {
			$submission = $this->submit();

			// if dreamform is used in API mode, return the submission state as JSON
			if ($mode === 'api') {
				$kirby->response()->code($submission->isSuccessful() ? 200 : 400);
				return json_encode(A::merge($submission->state()->toArray(), [
					'session' => Htmx::encrypt($submission->uuid()->toString())
				]), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_FORCE_OBJECT);
			}

			// if dreamform is used in htmx mode, return the enhanced HTML
			if ($mode === 'htmx' && $kirby->request()->header('Hx-Request') === 'true') {
				try {
					$page = DreamForm::findPageOrDraftRecursive(Htmx::decrypt($kirby->request()->body()->get('dreamform:page')));
					$attr = Json::decode(Htmx::decrypt($kirby->request()->body()->get('dreamform:attr')));

					// if an error is thrown, this means the data must have been tampered with
				} catch (Exception $e) {
					return t('dreamform.generic-error');
				}

				if ($submission->state()->get('redirect')->value()) {
					return new Response('', null, 200, [
						'Hx-Redirect' => $submission->state()->get('redirect')->value()
					]);
				}

				// Inject these variables in all snippets
				// similar to the page.render:before hook
				$kirby->data = [
					'kirby' => $kirby,
					'site' => $kirby->site(),
					'page' => $page,
					'submission' => $submission,
				];

				return snippet('dreamform/form', [
					'form' => $this,
					'attr' => $attr
				], true);
			}

			// continue with PRG submission
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
	 * Returns the form enctype based on the fields
	 */
	public function enctype(): string
	{
		if ($this->fields()->findBy('type', 'file-upload')) {
			return 'multipart/form-data';
		}

		return 'application/x-www-form-urlencoded';
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
			return $this->valueFromQuery($key);
		}

		return $submission->valueFor($key);
	}

	/**
	 * Returns the value of a field from the URL query
	 *
	 * TODO: check for field type and sanitize the value
	 */
	public function valueFromQuery(string $key): Field|null
	{
		$key = DreamForm::normalizeKey($key);

		if (!($value = App::instance()->request()->query()->get($key))) {
			return null;
		}

		$field = $this->fields()->findBy('key', $key);
		if (!$field) {
			$field = $this->fields()->findBy('id', $key);
		}

		if (!$field) {
			return null;
		}

		return $field->sanitize(new Field($this, $key, $value));
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
			if (!$field::hasValue()) {
				continue;
			}

			$type = Str::replace($field->block()->type(), '-field', '');
			$fields[$field->id()] = "{$field->block()->label()->or($field->key())} ({$type})";
		}

		return $fields;
	}

	/**
	 * Returns the modified permissions object for the form
	 */
	public function permissions(): FormPermissions
	{
		return new FormPermissions($this);
	}

	/**
	 * Make it impossible to duplicate stored submissions
	 */
	public function hasChildren(): bool
	{
		return false;
	}
}
