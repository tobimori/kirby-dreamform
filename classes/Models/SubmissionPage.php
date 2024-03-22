<?php

namespace tobimori\DreamForm\Models;

use DateTime;
use IntlDateFormatter;
use Kirby\Cms\App;
use Kirby\Cms\Blocks;
use Kirby\Cms\Collection;
use Kirby\Cms\File;
use Kirby\Cms\Responder;
use Kirby\Content\Content;
use Kirby\Content\Field;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Filesystem\F;
use Kirby\Http\Remote;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;
use Kirby\Toolkit\V;
use tobimori\DreamForm\DreamForm;
use tobimori\DreamForm\Fields\Field as FormField;

class SubmissionPage extends BasePage
{
	/**
	 * Returns the submission referer (for PRG redirects)
	 */
	public function referer(): string|null
	{
		return $this->content()->get('dreamform_referer')->value();
	}

	/**
	 * Returns the value of a field in the submission content by its ID
	 */
	public function valueForId(string $id): Field|null
	{
		/** @var tobimori\DreamForm\Fields\Field|null $field */
		$field = $this->form()->fields()->find($id);
		if ($field) {
			if (!($key = $field->key())) {
				return null;
			}

			return $this->content()->get($key);
		}

		return null;
	}

	/**
	 * Returns the value of a field from the URL query
	 */
	public static function valueFromQuery(string $key): Field|null
	{
		$key = Str::replace($key, '-', '_');
		if (!($value = App::instance()->request()->query()->get($key))) {
			return null;
		}

		return new Field(DreamForm::currentPage(), $key, $value);
	}

	/**
	 * Returns the value of a field in the submission content by its key
	 */
	public function valueFor(string $key): Field|null
	{
		$key = Str::replace($key, '-', '_');
		$field = $this->content()->get($key);
		if ($field->isEmpty()) {
			// check if the field is prefillable from url params
			$field = static::valueFromQuery($key);
		}

		return $field;
	}

	/**
	 * Returns the values of all fields in the submission content as content object
	 */
	public function values(): Content
	{
		$values = [];
		foreach ($this->form()->fields() as $field) {
			$values[$field->key()] = $this->valueFor($field->key());
		}

		return new Content($values, $this);
	}

	/**
	 * Returns the error message for a field in the submission state
	 */
	public function errorFor(string $key = null): string|null
	{
		if ($key === null) {
			return $this->state()->get('error')->value();
		}

		$key = Str::replace($key, '-', '_');
		$errors = $this->state()->get('errors')->toObject();
		return $errors->get($key)->value();
	}

	/**
	 * Sets an error in the submission state
	 */
	public function setError(string $message, string $field = null): static
	{
		$state = $this->state()->toArray();
		$state['success'] = false;
		if ($field) {
			$state['errors'][$field] = $message;
		} else {
			$state['error'] = $message;
		}

		// manually update content to avoid saving it to disk before the form submission is finished
		$this->content = $this->content()->update([
			'dreamform_state' => $state
		]);

		return $this;
	}

	/**
	 * Returns the raw field value from the request body
	 */
	public static function valueFromBody(string $key): string|null
	{
		$key = DreamForm::normalizeKey($key);
		$body = App::instance()->request()->body()->toArray();

		$body = array_combine(
			A::map(array_keys($body), function ($key) {
				return DreamForm::normalizeKey($key);
			}),
			array_values($body)
		);

		return $body[$key] ?? null;
	}

	/**
	 * Set a field with the value from the request
	 */
	public function updateFieldFromRequest(FormField $field): FormField
	{
		return $field->setValue(
			new Field(
				$this,
				$key = $field->key(),
				$this->valueFromBody($key)
			)
		);
	}

	/**
	 * Sets a field in the submission content
	 */
	public function setField(FormField $field): static
	{
		$this->content = $this->content()->update([
			$field->key() => $field->value()->value()
		]);

		return $this;
	}

	/**
	 * Create actions from the form's content
	 */
	public function createActions(Blocks $blocks = null): Collection
	{
		$blocks ??= $this->form()->content()->get('actions')->toBlocks();

		$actions = [];
		foreach ($blocks as $block) {
			$type = Str::replace($block->type(), '-action', '');

			$action = DreamForm::action($type, $block, $this);
			if ($action) {
				$actions[] = $action;
			}
		}

		return new Collection($actions, []);
	}

	/**
	 * Sets the redirect URL in the submission state
	 */
	public function setRedirect(string $url): static
	{
		$state = $this->state()->toArray();
		$state['redirect'] = $url;

		$this->content = $this->content()->update(['dreamform_state' => $state]);

		return $this;
	}

	/**
	 * Returns a Response that redirects the user to the URL set in the submission state
	 */
	public function redirect(): Responder
	{
		if (!$this->state()->get('redirect')->value()) {
			return $this->redirectToReferer();
		}

		return App::instance()->response()->redirect(
			$this->state()->get('redirect')->value()
		);
	}

	/**
	 * Returns a Response that redirects the user to the referer URL
	 */
	public function redirectToReferer(): Responder
	{
		return App::instance()->response()->redirect(
			$this->referer() ?? $this->site()->url()
		);
	}

	public function currentStep(): int
	{
		return $this->state()->get('step')->toInt();
	}

	public function advanceStep(): static
	{
		$available = count($this->form()->steps());
		if ($this->state()->get('step')->value() >= $available) {
			return $this;
		}

		$state = $this->state()->toArray();
		$state['step'] = $state['step'] + 1;
		$this->content = $this->content()->update(['dreamform_state' => $state]);

		return $this;
	}

	/**
	 * Finish the submission and save it to the disk
	 */
	public function finish(bool $saveToDisk = true): static
	{
		// set partial state for showing "success"
		$state = $this->state()->toArray();
		$state['partial'] = false;
		$this->content = $this->content()->update(['dreamform_state' => $state]);

		if ($saveToDisk) {
			// elevate permissions to save the submission
			App::instance()->impersonate('kirby');
			$submission = $this->save($this->content()->toArray(), App::instance()?->languages()?->default()?->code() ?? null);
			App::instance()->impersonate();
		}

		return $submission;
	}

	/**
	 * Returns a boolean whether the submission is finished
	 */
	public function isFinished(): bool
	{
		return !$this->state()->get('partial')->toBool();
	}

	/**
	 * Returns a boolean whether the submission was successful so far
	 */
	public function isSuccessful(): bool
	{
		return $this->state()->get('success')->toBool();
	}

	/**
	 * Returns the submission state as content object
	 */
	public function state(): Content
	{
		return $this->content()->get('dreamform_state')->toObject();
	}

	/** @var SubmissionPage|null */
	private static $session = null;

	/**
	 * Store submission in session for use with PRG pattern
	 */
	public function storeSession(): static
	{
		if (App::instance()->option('tobimori.dreamform.mode', 'prg') === 'api') {
			return $this;
		}

		App::instance()->session()->set(DreamForm::SESSION_KEY, $this);
		return static::$session = $this;
	}

	/**
	 * Pull submission from session
	 */
	public static function fromSession(): SubmissionPage|null
	{
		if (App::instance()->option('tobimori.dreamform.mode', 'prg') === 'api') {
			return null;
		}

		if (static::$session) {
			return static::$session;
		}

		static::$session = App::instance()->session()->get(DreamForm::SESSION_KEY, null);

		// remove it from the session for subsequent loads
		if (
			static::$session && ( // if the session exists
				static::$session->isFinished() // & if the submission is finished
				|| (static::$session->currentStep() === 1 && !static::$session->isSuccessful()) // or if it's the first step and not successful
			)
		) {
			App::instance()->session()->remove(DreamForm::SESSION_KEY);
		}

		return static::$session;
	}

	/**
	 * Return the corresponding form page
	 */
	public function form(): FormPage
	{
		$page = $this->parent();

		if ($page->intendedTemplate()->name() !== 'form') {
			throw new InvalidArgumentException('[kirby-dreamform] SubmissionPage must be a child of a FormPage');
		}

		return $page;
	}

	/**
	 * Format the submission date as integer for sorting
	 */
	public function sortDate(): string
	{
		return $this->content()->get('dreamform_submitted')->toDate();
	}

	/**
	 * Format the submission date as title for use in the panel
	 */
	public function title(): Field
	{
		$date = new DateTime($this->content()->get('dreamform_submitted')->value());
		return new Field($this, 'title', IntlDateFormatter::formatObject($date, IntlDateFormatter::MEDIUM));
	}

	/**
	 * Downloads a gravatar image for the submission, to be used in the panel as page icon.
	 */
	public function gravatar(): File|null
	{
		if (!App::instance()->option('tobimori.dreamform.integrations.gravatar', true)) {
			return null;
		}

		// if we previously found no image for the entry, we don't need to check again
		if ($this->content()->get('dreamform_gravatar')->toBool()) {
			return null;
		}

		if ($this->file('gravatar.jpg')) {
			return $this->file('gravatar.jpg');
		}

		// Find the first email in the content
		foreach ($this->content()->data() as $value) {
			if (V::email($value)) {
				// trim & lowercase the email
				$value = Str::lower(Str::trim($value));
				$hash = hash('sha256', $value);


				$request = Remote::get("https://www.gravatar.com/avatar/{$hash}?d=404");
				if ($request->code() === 200) {
					// TODO: check if we need a temp file or if we can use the content directly?
					F::write($tmpPath = $this->root() . '/tmp.jpg', $request->content());
					$file = $this->createFile([
						'filename' => 'gravatar.jpg',
						'source' => $tmpPath,
						'parent' => $this
					]);
					F::remove($tmpPath);

					return $file;
				}
			}
		}

		$this->update([
			'dreamform_gravatar' => false
		]);

		return null;
	}
}
