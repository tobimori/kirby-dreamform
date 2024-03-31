<?php

namespace tobimori\DreamForm\Support;

use Kirby\Cms\App;

/**
 * Helper class for customizing the panel menu
 */
final class Menu
{
	private function __construct()
	{
		throw new \Error('This class cannot be instantiated');
	}

	/**
	 * Returns the current path
	 */
	private static function path()
	{
		return App::instance()->request()->path()->toString();
	}

	/**
	 * Returns the path to the forms page
	 */
	public static function formPath()
	{
		$formsPage = App::instance()->site()->findPageOrDraft(App::instance()->option('tobimori.dreamform.page'));
		return $formsPage->panel()->path();
	}

	/**
	 * Returns the menu item for the forms page
	 */
	public static function forms()
	{
		if (App::instance()->user()->role()->permissions()->for('tobimori.dreamform', 'accessForms') === false) {
			return null;
		}

		return [
			'label' => t('dreamform.forms'),
			'link' => static::formPath(),
			'icon' => 'survey',
			'current' => fn () =>
			str_contains(static::path(), static::formPath())
		];
	}

	/**
	 * Returns the menu item for the submissions page
	 */
	public static function site()
	{
		return [
			'current' => fn (string|null $id) => $id === 'site' && !str_contains(static::path(), static::formPath())
		];
	}
}
