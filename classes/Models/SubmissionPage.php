<?php

namespace tobimori\DreamForm\Models;

use DateTime;
use IntlDateFormatter;
use Kirby\Cms\App;
use Kirby\Cms\Blocks;
use Kirby\Cms\Collection;
use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Cms\Responder;
use Kirby\Content\Content;
use Kirby\Content\Field;
use Kirby\Content\PlainTextStorage;
use Kirby\Content\VersionId;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Filesystem\F;
use Kirby\Http\Remote;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;
use Kirby\Toolkit\V;
use tobimori\DreamForm\DreamForm;
use tobimori\DreamForm\Fields\Field as FormField;
use tobimori\DreamForm\Models\Log\HasSubmissionLog;
use tobimori\DreamForm\Permissions\SubmissionPermissions;
use tobimori\DreamForm\Storage\SubmissionSessionStorage;
use tobimori\DreamForm\Storage\SubmissionCacheStorage;
use tobimori\DreamForm\Support\Htmx;

/**
 * The submission page is the heart of the plugin.
 *
 * It's the represenation of a form submission,
 * and contains logic to handle the complete submission process.
 */
class SubmissionPage extends BasePage
{
	use HasSubmissionLog;
	use SubmissionMetadata;
	use SubmissionSession;
	use SubmissionHandling;

	/**
	 * Creates a new submission page object
	 */
	public function __construct(array $props)
	{
		parent::__construct($props);

		if (!$this->exists()) {
			$mode = DreamForm::option('mode', 'prg');

			if ($mode === 'api' || (Htmx::isActive() && Htmx::isHtmxRequest())) {
				$this->changeStorage(SubmissionCacheStorage::class);
			} else {
				$this->changeStorage(SubmissionSessionStorage::class);
			}
		}
	}

	/**
	 * Returns the submission referer (for PRG redirects)
	 */
	public function referer(): string|null
	{
		$referer = $this->content()->get('dreamform_referer');
		if (is_array($referer->value())) {
			return null;
		}

		return $referer->value();
	}

	/**
	 * Looks up the referer as page in the site structure
	 *
	 * If your pages use custom URLs (by overriding the url() method),
	 * this method may not be able to find the page. You can use the
	 * 'refererPageResolver' config option to implement custom page
	 * resolution logic.
	 */
	public function findRefererPage(): Page|null
	{
		if (!$this->referer()) {
			return null;
		}

		// this is not using DreamForm::option function
		// since that would resolve our callable without input
		$resolver = App::instance()->option("tobimori.dreamform.refererPageResolver");
		if (is_callable($resolver)) {
			return $resolver($this->referer(), $this);
		} elseif ($page = DreamForm::findPageOrDraftRecursive($this->referer())) {
			return $page;
		}

		return null;
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
	 * Sets the redirect URL in the submission state
	 */
	public function setRedirect(string $url): static
	{
		return $this->updateState(['redirect' => $url]);
	}

	/**
	 * Returns a Response that redirects the user to the referer URL
	 */
	public function redirectToReferer(): Responder
	{
		$kirby = App::instance();
		$append = "#{$this->form()->uuid()->id()}";
		if (DreamForm::option('mode') !== 'api' && $kirby->option('cache.pages.active') === true) {
			$append = '?x=' . $append;
		}

		return  $kirby->response()->redirect(
			($this->referer() ?? $this->site()->url()) . $append
		);
	}

	/**
	 * Returns the value of a field in the submission content by its ID
	 */
	public function valueForId(string $id): Field|null
	{
		/** @var tobimori\DreamForm\Fields\Field|null $field */
		$field = $this->form()->formFields()->find($id);
		if ($field) {
			if (!($key = $field->key())) {
				return null;
			}

			return $this->content()->get($key);
		}

		return null;
	}

	/**
	 * Returns the static or dynamic value of a dynamic field
	 */
	public function valueForDynamicField(Field $field): Field|null
	{
		$field = $field->toObject();
		// type: 'static' or 'dynamic'
		// field: null or the field id
		// value: null or the value

		$type = $field->type()->value();
		if ($type === 'dynamic' && $field->field()->isNotEmpty()) {
			return $this->valueForId($field->field()->value());
		}

		if ($type === 'static' && $field->value()->isNotEmpty()) {
			return $field->value();
		}

		return null;
	}

	/**
	 * Returns the value of a field in the submission content by its key
	 */
	public function valueFor(string $key): Field|null
	{
		$key = DreamForm::normalizeKey($key);
		$field = $this->content()->get($key);
		if ($field->isEmpty()) {
			// check if the field is prefillable from url params
			$field = $this->parent()->valueFromQuery($key);
		}

		return $field;
	}

	/**
	 * Returns the values of all fields in the submission content as content object
	 */
	public function values(): Content
	{
		$values = [];
		foreach ($this->form()->formFields() as $field) {
			if ($field::hasValue()) {
				$values[$field->key()] = $this->valueFor($field->key());
			}
		}

		return new Content($values, $this);
	}

	/**
	 * Returns the error message for a field in the submission state
	 */
	public function errorFor(string|null $key = null, FormPage|null $form = null): string|null
	{
		if (!$form?->is($this->form())) {
			return null;
		}

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
	public function setError(string $message, string|null $field = null): static
	{
		$state = $this->state()->toArray();
		$state['success'] = false;
		if ($field) {
			$state['errors'][$field] = $message;
		} else {
			$state['error'] = $message;
		}

		$this->version(VersionId::LATEST)->update(['dreamform_state' => $state]);
		return $this;
	}

	/**
	 * Removes an error from the submission state
	 */
	public function removeError(string|null $field = null): static
	{
		$state = $this->state()->toArray();
		if ($field) {
			unset($state['errors'][$field]);
		} else {
			$state['error'] = null;
		}

		if (empty($state['errors']) && !$state['error']) {
			$state['success'] = true;
		}

		$this->version(VersionId::LATEST)->update(['dreamform_state' => $state]);
		return $this;
	}

	/**
	 * Returns the raw field value from the request body
	 */
	public static function valueFromBody(string $key): mixed
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
		App::instance()->impersonate('kirby', fn () => $this->version(VersionId::LATEST)->update([$field->key() => $field->value()->value()]));
		return $this;
	}

	/**
	 * Create actions from the form's content
	 */
	public function createActions(Blocks|null $blocks = null, bool $force = false): Collection
	{
		$blocks ??= $this->form()->content()->get('actions')->toBlocks();

		$actions = [];
		foreach ($blocks as $block) {
			$type = Str::replace($block->type(), '-action', '');

			$action = DreamForm::action($type, $block, $this, $force);
			if ($action) {
				$actions[] = $action;
			}
		}

		return new Collection($actions);
	}

	/**
	 * Returns the action state
	 */
	public function actionState(): Content|null
	{
		if (!$this->actionsDidRun()) {
			return null;
		}

		return $this->state()->actions()->toObject();
	}

	/**
	 * Sets the action state of the submission
	 */
	public function setActionState(array $data): static
	{
		$state = $this->state()->toArray();
		if (is_bool($state['actions'])) {
			$state['actions'] = [];
		}

		return $this->updateState(['actions' => A::merge($state['actions'], $data)]);
	}

	/**
	 * Returns a boolean whether the actions have run
	 */
	public function actionsDidRun(): bool
	{
		return $this->state()->get('actionsDidRun')->toBool();
	}

	/**
	 * Returns the current step of the submission
	 */
	public function currentStep(): int
	{
		return $this->state()->get('step')->toInt();
	}

	/**
	 * Returns whether the submission is about to be finished
	 */
	public function isFinalStep(): bool
	{
		return !$this->form()->isMultiStep() || $this->currentStep() === count($this->form()->steps());
	}

	/**
	 * Advance the submission to the next step
	 */
	public function advanceStep(): static
	{
		$available = count($this->form()->steps());
		if ($this->state()->get('step')->value() >= $available) {
			return $this;
		}

		$state = $this->state()->toArray();
		$this->updateState(['step' => $state['step'] + 1]);

		return $this;
	}


	/**
	 * Finish the submission and save it to the disk
	 *
	 * TODO: merge with $submission->finalize()?
	 */
	public function finish(bool $saveToDisk = true): static
	{
		// set partial state for showing "success"
		$state = $this->state()->toArray();
		$state['partial'] = false;
		App::instance()->impersonate('kirby', fn () => $this->version(VersionId::LATEST)->update(['dreamform_state' => $state]));

		$submission = $this->applyHook('after');

		// $saveToDisk is used by silent performer exception to not store the submission
		// however when partial submissions are enabled, the action would be executed after the form has been partially saved already
		// so when $saveToDisk is false, and partial submissions are enabled we save anyway (?)
		// TBH: this might be unwanted behaviour - but i'm now sure how to handle this otherwise? delete the submission but keep partial?
		// (this might also be an issue in previous versions with multi-step forms since they also save partials)
		if ($saveToDisk || $this->form()->partialSubmissions()->toBool() && DreamForm::option('partialSubmissions') === true) {
			return $submission->saveSubmission();
		}

		return $submission;
	}

	/**
	 * Save the submission to the disk
	 */
	public function saveSubmission(): static
	{
		if (
			DreamForm::option('storeSubmissions', true) !== true
			|| !$this->form()->storeSubmissions()->toBool()
		) {
			return $this;
		}

		// check if content exists to save request (don't save empty submissions)
		$hasContent = false;
		foreach ($this->values()->toArray() as $value) {
			if ($value !== null) {
				$hasContent = true;
				break;
			}
		}

		if (!$hasContent) {
			return $this;
		}

		// store uuid
		$this->uuid()->populate();

		// If using temporary storage, persist to disk
		$storage = $this->storage();
		if ($storage instanceof SubmissionSessionStorage || $storage instanceof SubmissionCacheStorage) {
			return $this->changeStorage(PlainTextStorage::class);
		}

		// Already using PlainTextStorage
		return $this;
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

	/**
	 * Update the submission state
	 */
	public function updateState(array $data): static
	{
		$this->version(VersionId::LATEST)->update(['dreamform_state' => array_merge($this->state()->toArray(), $data)]);
		return $this;
	}

	/**
	 * Returns the submission state as array
	 */
	public function isSpam(): bool
	{
		return $this->state()->get('spam')->toBool();
	}

	/**
	 * Returns a boolean whether the submission is ham
	 */
	public function isHam(): bool
	{
		return !$this->isSpam();
	}

	/**
	 * Mark the submission as spam
	 */
	public function markAsSpam(bool $initial = false): static
	{
		if (!$initial) {
			// report the submission as spam to all guards
			// submits false negatives to akismet, etc.
			foreach ($this->form()->guards() as $guard) {
				$guard->reportSubmissionAsSpam($this);
			}
		}

		return $this->updateState(['spam' => true]);
	}

	/**
	 * Mark the submission as ham
	 */
	public function markAsHam(bool $initial = false): static
	{
		if (!$initial) {
			// report the submission as ham to all guards
			// submits false positives to akismet, etc.
			foreach ($this->form()->guards() as $guard) {
				$guard->reportSubmissionAsHam($this);
			}
		}

		return $this->updateState(['spam' => false]);
	}

	/**
	 * Returns the status of the submission
	 */
	public function status(): string
	{
		if ($this->isSpam()) {
			return 'draft';
		}

		return $this->isFinished() ? 'listed' : 'unlisted';
	}

	/**
	 * Check if the submission has any filled values
	 */
	public function isEmpty(): bool
	{
		foreach ($this->values()->toArray() as $key => $value) {
			if ($value !== null && $value !== '' && $value !== []) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Return the corresponding form page
	 */
	public function form(): FormPage
	{
		$page = $this->parent();

		if ($page->intendedTemplate()->name() !== 'form') {
			throw new InvalidArgumentException('[DreamForm] SubmissionPage must be a child of a FormPage');
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
		if (!DreamForm::option('integrations.gravatar', true)) {
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

		App::instance()->impersonate('kirby', fn () => $this->update([
			'dreamform_gravatar' => false
		]));

		return null;
	}

	/**
	 * Permissions check for the submission page
	 */
	public function isAccessible(): bool
	{
		if (!App::instance()->user()->role()->permissions()->for('tobimori.dreamform', 'accessSubmissions')) {
			return false;
		}

		return parent::isAccessible();
	}

	/**
	 * Returns the permissions object for this page
	 */
	public function permissions(): SubmissionPermissions
	{
		return new SubmissionPermissions($this);
	}

	/**
	 * Returns the content, always in the current language
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException If the language for the given code does not exist
	 */
	public function content(string|null $languageCode = null): Content
	{
		return parent::content(App::instance()->defaultLanguage()?->code() ?? null);
	}
}
