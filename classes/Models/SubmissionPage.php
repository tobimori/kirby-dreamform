<?php

namespace tobimori\DreamForm\Models;

use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\Content\Field;

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
}
