<?php

namespace tobimori\DreamForm\Models;

use DateTime;
use IntlDateFormatter;
use Kirby\Cms\App;
use Kirby\Cms\Blocks;
use Kirby\Cms\Collection;
use Kirby\Cms\File;
use Kirby\Cms\Page;
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
	private Page|null $referer = null;

	public function referer(): Page|null
	{
		if ($this->referer) {
			return $this->referer;
		}

		return $this->referer = App::instance()->site()->findPageOrDraft($this->content()->get('dreamform_referer')->value());
	}

	public function getFieldById(string $id): Field|null
	{
		/** @var FormPage $parent */
		$parent = $this->parent();

		/** @var tobimori\DreamForm\Fields\Field|null $field */
		$field = $parent->fields()->find($id);
		if ($field) {
			if (!($key = $field->block()->key()->value())) {
				return null;
			}

			return $this->content()->get($key);
		}

		return null;
	}

	/**
	 * Sets an error in the submission state
	 */
	public function setError(string $message, string $field = null): static
	{
		$state = $this->content()->get('dreamform-state')->value();
		$state['success'] = false;
		if ($field) {
			$state['errors'][$field] = $message;
		} else {
			$state['error'] = $message;
		}

		// manually update content to avoid saving it to disk before the form submission is finished
		$this->content = $this->content()->update([
			'dreamform-state' => $state
		]);

		return $this;
	}

	/**
	 * Creates a field with the value from the request
	 */
	public function createFieldFromRequest(FormField $field): FormField
	{
		$raw = App::instance()->request()->body()->get($key = $field->key()) ?? null;
		$field->setValue(new Field($this, $key, $raw));

		return $field;
	}

	/**
	 * Sets a field in the submission content
	 */
	public function setField(FormField $field): static
	{
		$previousFields = $this->content()->get('dreamform-values')->toArray();
		$this->content = $this->content()->update([
			'dreamform-values' => A::merge($previousFields, [
				$field->key() => $field->value()->value()
			])
		]);

		return $this;
	}

	/**
	 * Create actions from the form's content
	 */
	public function createActions(Blocks $blocks = null): Collection
	{
		$blocks ??= $this->form()->content()->get('actions')->toBlocks();

		$active = option('tobimori.dreamform.actions', true);
		$registered = FormPage::$registeredActions;
		$actions = [];

		foreach ($blocks as $block) {
			$type = Str::replace($block->type(), '-action', '');

			// check if the action wanted is registered
			if (!key_exists($type, $registered)) {
				continue;
			}

			// check if the action wanted is set as active in config
			if (is_array($active) && !in_array($type, $active) || $active != true) {
				continue;
			}

			// check if the action is available
			// (e.g. MailchimpAction requires the Mailchimp API to be set up)
			if ($registered[$type]::isAvailable() === false) {
				continue;
			}

			$actions[] = new $registered[$type]($block, $this);
		}

		return new Collection($actions, []);
	}

	/**
	 * Sets the redirect URL in the submission state
	 */
	public function setRedirect(string $url): static
	{
		$state = $this->content()->get('dreamform-state')->value();
		$state['redirect'] = $url;

		$this->content = $this->content()->update([
			'dreamform-state' => $state
		]);

		return $this;
	}

	public function redirect(): string|null
	{
		return App::instance()->response()->redirect(
			$this->state()->get('redirect')->value()
		);
	}
	/**
	 * Finish the submission and save it to the disk
	 */
	public function finish(): static
	{
		return $this->save($this->content()->toArray());
	}

	public function isSuccessful(): bool
	{
		return $this->state()->get('success')->toBool();
	}

	public function state(): Content
	{
		return $this->content()->get('dreamform-state')->toObject();
	}

	/**
	 * Store submission in session for use with PRG pattern
	 */
	public function storeSession(): static
	{
		App::instance()->session()->set(DreamForm::SESSION_KEY, $this);
		return $this;
	}

	/**
	 * Pull submission from session
	 */
	public static function fromSession(): SubmissionPage|null
	{
		return App::instance()->session()->get(DreamForm::SESSION_KEY, null);
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
		if ($this->content()->get('dreamform-gravatar')->toBool()) {
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
			'dreamform-gravatar' => false
		]);

		return null;
	}
}
