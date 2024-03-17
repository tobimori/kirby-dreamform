<?php

namespace tobimori\DreamForm;

use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\Toolkit\Str;
use tobimori\DreamForm\Actions\Action;
use tobimori\DreamForm\Fields\Field;

final class DreamForm
{
	public const SESSION_KEY = 'dreamform.submission';

	// TODO: refactor
	private static $registeredGuards = [];
	private static $registeredFields = [];
	private static $registeredActions = [];

	public static function registerGuard(string $type, string $class)
	{
		static::$registeredGuards[$type] = $class;
	}

	public static function registerGuards(string ...$guards)
	{
		foreach ($guards as $guard) {
			static::registerGuard($guard::type(), $guard);
		}
	}

	public static function guards(): array
	{
		$active = App::instance()->option('tobimori.dreamform.guards', true);
		$registered = static::$registeredGuards;

		$guards = [];
		foreach ($registered as $type => $guard) {
			if (is_array($active) ? !in_array($type, $active) : $active !== true) {
				continue;
			}

			if (!$guard::isAvailable()) {
				continue;
			}

			$guards[$type] = $guard;
		}

		return $guards;
	}

	public static function guard(string $type, mixed ...$data): Performer|null
	{
		$guards = DreamForm::guards();
		if (!key_exists($type, $guards)) {
			return null;
		}

		$guard = static::$registeredGuards[$type];
		return new $guard(...$data);
	}

	public static function registerField(string $type, string $class)
	{
		static::$registeredFields[$type] = $class;
	}

	public static function registerFields(string ...$fields)
	{
		foreach ($fields as $field) {
			static::registerField($field::type(), $field);
		}
	}

	public static function fields()
	{
		$active = App::instance()->option('tobimori.dreamform.fields', true);
		$registered = static::$registeredFields;

		$fields = [];
		foreach ($registered as $type => $field) {
			if (is_array($active) ? !in_array($type, $active) : $active !== true) {
				continue;
			}

			if (!$field::isAvailable()) {
				continue;
			}

			$fields[$type] = $field;
		}

		return $fields;
	}


	public static function field(string $type, mixed ...$data): Field|null
	{
		$fields = DreamForm::fields();
		if (!key_exists($type, $fields)) {
			return null;
		}

		$field = static::$registeredFields[$type];
		return new $field(...$data);
	}

	public static function registerAction(string $type, string $class)
	{
		static::$registeredActions[$type] = $class;
	}

	public static function registerActions(string ...$actions)
	{
		foreach ($actions as $action) {
			static::registerAction($action::type(), $action);
		}
	}

	public static function actions()
	{
		$active = App::instance()->option('tobimori.dreamform.actions', true);
		$registered = static::$registeredActions;

		$actions = [];
		foreach ($registered as $type => $action) {
			if (is_array($active) ? !in_array($type, $active) : $active !== true) {
				continue;
			}

			if (!$action::isAvailable()) {
				continue;
			}

			$actions[$type] = $action;
		}

		return $actions;
	}

	public static function action(string $type, mixed ...$data): Action|null
	{
		$actions = DreamForm::actions();
		if (!key_exists($type, $actions)) {
			return null;
		}

		$action = static::$registeredActions[$type];
		return new $action(...$data);
	}

	/**
	 * Get the page the request was made from using the URL path
	 */
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

	/**
	 * Returns the debug mode option for the plugin
	 */
	public static function debugMode(): bool
	{
		$option = App::instance()->option('tobimori.dreamform.debug');
		if (is_callable($option)) {
			$option = $option();
		}

		return $option;
	}
}
