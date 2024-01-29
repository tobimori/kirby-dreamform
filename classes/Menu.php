<?php

namespace tobimori\DreamForm;

use Kirby\Cms\App;

/**
 * Helper class for customizing the panel menu
 */
final class Menu
{
	private static function path()
	{
		return App::instance()->request()->path()->toString();
	}

	public static function formPath()
	{
		$formsPage = App::instance()->site()->findPageOrDraft(App::instance()->option('tobimori.dreamform.page'));
		return $formsPage->panel()->path();
	}

	public static function forms()
	{
		return [
			'label' => t('forms'),
			'link' => static::formPath(),
			'icon' => 'survey',
			'current' => fn () =>
			str_contains(static::path(), static::formPath())
		];
	}

	public static function site()
	{
		return [
			'current' => fn (string $id) => $id === 'site' && !str_contains(static::path(), static::formPath())
		];
	}
}
