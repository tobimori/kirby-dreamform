<?php

namespace tobimori\DreamForm;

use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\Toolkit\Str;
use tobimori\DreamForm\Actions\Action;
use tobimori\DreamForm\Fields\Field;
use tobimori\DreamForm\Guards\Guard;

final class DreamForm
{
	/**
	 * The session key for storing the submission data
	 */
	public const SESSION_KEY = 'dreamform.submission';

	/**
	 * Stores registered guards
	 */
	private static array $registeredGuards = [];

	/**
	 * Registers a guard class with a custom type
	 */
	public static function registerGuard(string $type, string $class): void
	{
		static::$registeredGuards[$type] = $class;
	}

	/**
	 * Returns all registered and active guard classes
	 */
	public static function guards(): array
	{
		$active = App::instance()->option('tobimori.dreamform.guards', ['csrf']);
		$registered = static::$registeredGuards;

		$guards = [];
		foreach ($registered as $type => $guard) {
			if (!in_array($type, $active)) {
				continue;
			}

			if (!$guard::isAvailable()) {
				continue;
			}

			$guards[$type] = $guard;
		}

		return $guards;
	}

	/**
	 * Stores registered fields
	 */
	private static array $registeredFields = [];

	/**
	 * Registers a field class with a custom type
	 */
	public static function registerField(string $type, string $class)
	{
		static::$registeredFields[$type] = $class;
	}

	/**
	 * Returns all registered and active field classes
	 */
	public static function fields(): array
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

	/**
	 * Create a field instance from the registered fields
	 */
	public static function field(string $type, mixed ...$data): Field|null
	{
		$fields = DreamForm::fields();
		if (!key_exists($type, $fields)) {
			return null;
		}

		$field = static::$registeredFields[$type];
		return new $field(...$data);
	}

	/**
	 * Stores registered actions
	 */
	private static array $registeredActions = [];

	/**
	 * Registers an action class with a custom type
	 */
	public static function registerAction(string $type, string $class): void
	{
		static::$registeredActions[$type] = $class;
	}

	/**
	 * Returns all registered and active action classes
	 */
	public static function actions(): array
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

	/**
	 * Create an action instance from the registered actions
	 */
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
	 * Register multiple classes at once using the generic type
	 * If you need to override the type, use the type-specific register method after DreamForm is loaded
	 */
	public static function register(string ...$classes)
	{
		foreach ($classes as $class) {
			if (is_subclass_of($class, Guard::class)) {
				static::registerGuard($class::type(), $class);
			} elseif (is_subclass_of($class, Field::class)) {
				static::registerField($class::type(), $class);
			} elseif (is_subclass_of($class, Action::class)) {
				static::registerAction($class::type(), $class);
			}
		}
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

	/**
	 * We need to normalize keys since Kirby does not support dashes in field names properly
	 */
	public static function normalizeKey(string $key): string
	{
		return Str::replace($key, '-', '_');
	}
}
