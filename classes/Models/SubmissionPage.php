<?php

namespace tobimori\DreamForm\Models;

use DateTime;
use IntlDateFormatter;
use Kirby\Cms\App;
use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Content\Field;
use Kirby\Filesystem\F;
use Kirby\Http\Remote;
use Kirby\Toolkit\Str;
use Kirby\Toolkit\V;
use tobimori\DreamForm\DreamForm;

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


	public function title(): Field
	{
		$date = new DateTime($this->content()->get('dreamform_submitted')->value());
		return new Field($this, 'title', IntlDateFormatter::formatObject($date, IntlDateFormatter::MEDIUM));
	}

	public function getFieldById(string $id): Field|null
	{
		/** @var FormPage $parent */
		$parent = $this->parent();

		/** @var tobimori\DreamForm\Fields\Field|null $field */
		$field = $parent->fields()->find($id);
		if ($field) {
			if (!($key = $field->field()->key()->value())) {
				return null;
			}

			return $this->content()->get($key);
		}

		return null;
	}

	public function storeSession(): static
	{
		App::instance()->session()->set(DreamForm::SESSION_KEY, $this);
		return $this;
	}

	public static function fromSession(): SubmissionPage|null
	{
		return App::instance()->session()->get(DreamForm::SESSION_KEY, null);
	}

	public function sortDate(): string
	{
		return $this->content()->get('dreamform_submitted')->toDate();
	}

	/**
	 * Downloads a gravatar image for the submission,
	 * to be used in the panel as page icon.
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
