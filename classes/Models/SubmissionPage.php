<?php

namespace tobimori\DreamForm\Models;

use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\Content\Field;
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
		return new Field($this, 'title', $this->content()->get('dreamform_submitted')->value());
	}

	public function getFieldById(string $id): Field|null
	{
		/** @var FormPage $parent */
		$parent = $this->parent();

		/** @var tobimori\DreamForm\Fields\Field|null $field */
		$field = $parent->fields()->find($id);
		if ($field) {
			return $this->content()->get($field->field()->key()->value());
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
}
