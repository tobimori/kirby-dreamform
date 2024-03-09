<?php

namespace tobimori\DreamForm;

use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\Toolkit\Str;

final class DreamForm
{
	public const SESSION_KEY = '_dreamform_submission';

	public static function currentPage(): Page|null
	{
		$path = App::instance()->request()->url()->toString();
		$matches = Str::match($path, "/pages\/([a-zA-Z0-9-_+]+)\/?/m");
		if (!$matches) {
			return null;
		}
		$page = App::instance()->site()->findPageOrDraft(Str::replace($matches[1], '+', '/'));

		return $page;
	}
}
