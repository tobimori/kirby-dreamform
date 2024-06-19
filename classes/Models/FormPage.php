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
use tobimori\DreamForm\Guards\LicenseGuard;
use tobimori\DreamForm\Permissions\FormPermissions;
use tobimori\DreamForm\Support\Htmx;

/**
 * The form page stores the form configuration and is used
 * to trigger the submission process.
 */
class FormPage extends BasePage
{
	/** @var \Kirby\Cms\Collection[] */
	private array $fields;

	/** @var \Kirby\Cms\Layouts[] */
	private array $steps;

	/**
	 * Returns the title field or the slug as fallback
	 */
	public function title(): Field
	{
		return $this->content()->get('title')->or($this->slug());
	}

	/**
	 * Returns additional attributes needed for HTMX support if such is enabled
	 */
	public function htmxAttr(Page $page, array $attr, SubmissionPage|null $submission): array
	{
		if (!Htmx::isActive()) {
			return [];
		}

		$htmx = [
			'hx-post' => $this->url(),
			'hx-swap' => 'outerHTML show:top',
			'hx-vals' => Json::encode(array_filter([
				'dreamform:session' => $submission && $submission?->form()->is($this) && $this->isMultiStep() ?
					Htmx::encrypt(
						($submission->exists() ? "page://" : "") . $submission->slug()
					) : null,
				'dreamform:page' => Htmx::encrypt($page->uuid()->toString()),
				'dreamform:attr' => Htmx::encrypt(Json::encode($attr))
			], fn ($value) => $value !== null))
		];

		return $htmx;
	}

	/**
	 * Returns the attributes for the form as array
	 */
	public function attr(): array
	{
		$attr = [
			'id' => $this->elementId(),
			'enctype' => $this->enctype(),
			'method' => 'POST',
			'novalidate' => 'novalidate',
			(DreamForm::option('useDataAttributes') ? 'data-form-url' : 'action') => $this->url()
		];

		return $attr;
	}

	public function elementId(string $suffix = ''): string
	{
		return $this->uuid()->id() . ($suffix ? "/{$suffix}" : '');
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
		if ($submission && $submission->form()->is($this)) {
			return $this->layouts($submission->currentStep());
		}

		return $this->layouts();
	}

	/**
	 * Returns an array of steps for a multi-step form
	 * This is an array, because Kirby's collection class does not allow
	 * for empty IDs and Layouts instances can't have an ID
	 *
	 * @return \Kirby\Cms\Layouts[]
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
	 * @return \tobimori\DreamForm\Guards\Guard[]
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
				'dreamform_log' => [],
				'dreamform_sender' => [],
				'dreamform_state' => [
					'success' => true,
					'partial' => true,
					'spam' => false,
					'step' => 1,
					'redirect' => null, // this is a redirect URL for the form
					'error' => null, // this is a common error message for the whole form
					'errors' => [], // this is an array of field-specific error messages
					'actions' => [], // this is an array of action data
					'actionsDidRun' => false,
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
		if (!$submission->form()->is($this)) {
			$submission = $this->initSubmission();
		}

		/**
		 * The form submission process is split into multiple steps
		 * Each step is a separate function
		 */
		try {
			$submission = $submission
				->applyHook('before')
				->collectMetadata()
				->handleGuards()
				->handleFields()
				->handleGuards(postValidation: true)
				->handleActions()
				->finalize()
				->handleAfterSubmitFields();
		} catch (Exception $e) {
			if ($e instanceof PerformerException) {
				// if the exception is silent, stop the form submission early as "successful"
				if ($e->isSilent()) {
					return $submission->storeSession()->finish(false);
				}
			}

			$submission->setError($e->getMessage());
		}

		// store the submission in the session
		return $submission->storeSession();
	}

	/**
	 * Handles the form submission actions
	 *
	 * @deprecated Use $submission->handleActions() instead
	 */
	public function handleActions(SubmissionPage $submission): SubmissionPage
	{
		return $submission->handleActions();
	}

	/**
	 * Runs the form handling, or renders a 404 page
	 */
	public function render(array $data = [], $contentType = 'html'): string
	{
		$kirby = App::instance();
		$mode = DreamForm::option('mode', 'prg');

		if ($kirby->request()->method() === 'POST') {
			$submission = $this->submit();

			// if dreamform is used in API mode, return the submission state as JSON
			if ($mode === 'api') {
				$kirby->response()->code($submission->isSuccessful() ? 200 : 400);
				return json_encode(A::merge($submission->state()->toArray(), $this->isMultiStep() ? [
					'session' => Htmx::encrypt($submission->slug())
				] : []), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_FORCE_OBJECT);
			}

			// if dreamform is used in htmx mode, return the enhanced HTML
			if ($mode === 'htmx' && Htmx::isHtmxRequest()) {
				try {
					$page = DreamForm::findPageOrDraftRecursive(Htmx::decrypt($kirby->request()->body()->get('dreamform:page')));
					$attr = Json::decode(Htmx::decrypt($kirby->request()->body()->get('dreamform:attr')));

					// if an error is thrown, this means the data must have been tampered with
				} catch (Exception $e) {
					return t('dreamform.submission.error.generic');
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
				throw new Exception(tt('dreamform.form.error.duplicateKey', ['key' => $key]));
			}

			if (Str::startsWith($key, 'dreamform')) {
				throw new Exception(tt('dreamform.form.error.reservedKey', ['key' => $key]));
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
		if (!$submission || !$submission->form()->is($this)) {
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
		return SubmissionPage::fromSession()?->errorFor($key, $this);
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
