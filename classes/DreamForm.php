<?php

namespace tobimori\DreamForm;

use Closure;
use Kirby\Cms\App;
use Kirby\Cms\Block;
use Kirby\Cms\Page;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Plugin\Plugin;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;
use tobimori\DreamForm\Actions\Action;
use tobimori\DreamForm\Fields\Field;
use tobimori\DreamForm\Guards\Guard;
use tobimori\DreamForm\Models\FormPage;

/**
 * Main class for the plugin
 * Contains registry for performers & fields
 */
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
	 * @deprecated Use the tobimori.dreamform.guards plugin key instead; removed in v3
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
		$active = DreamForm::option('guards.available', ['csrf']);
		$registered = static::extensions('guards', Guard::class, static::$registeredGuards);

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
	 * @deprecated Use the tobimori.dreamform.fields plugin key instead; removed in v3
	 */
	public static function registerField(string $type, string $class)
	{
		static::$registeredFields[$type] = $class;
	}

	/**
	 * Returns all registered and active field classes
	 */
	public static function fields(FormPage|null $formPage = null): array
	{
		$active = DreamForm::option('fields.available', true);
		$registered = static::extensions('fields', Field::class, static::$registeredFields);

		$fields = [];
		foreach ($registered as $type => $field) {
			if (is_array($active) ? !in_array($type, $active) : $active !== true) {
				continue;
			}

			if (!$field::isAvailable($formPage)) {
				continue;
			}

			$fields[$type] = $field;
		}

		return $fields;
	}

	/**
	 * Create a field instance from the registered fields
	 */
	public static function field(string $type, Block $block, FormPage|null $formPage = null): Field|null
	{
		$fields = DreamForm::fields($formPage);
		if (!key_exists($type, $fields)) {
			return null;
		}

		$field = $fields[$type];
		return new $field($block);
	}

	/**
	 * Stores registered actions
	 */
	private static array $registeredActions = [];

	/**
	 * Registers an action class with a custom type
	 * @deprecated Use the tobimori.dreamform.actions plugin key instead; removed in v3
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
		$active = DreamForm::option('actions.available', true);
		$registered = static::extensions('actions', Action::class, static::$registeredActions);

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

		$action = $actions[$type];
		return new $action(...$data);
	}

	/**
	 * Register multiple classes at once using the generic type
	 * If you need to override the type, use the type-specific register method after DreamForm is loaded
	 * @deprecated Use the tobimori.dreamform.fields, .actions or .guards plugin keys instead; removed in v3
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
	 * Read plugin extensions lazily, with built-ins first and legacy registrations last
	 */
	private static function extensions(string $kind, string $baseClass, array $legacy): array
	{
		$plugins = App::instance()->plugins();
		$plugins = ['tobimori/dreamform' => $plugins['tobimori/dreamform'] ?? null] + $plugins;
		$key = "tobimori.dreamform.{$kind}";
		$registered = [];

		foreach ($plugins as $plugin) {
			if (!$plugin instanceof Plugin) {
				continue;
			}

			$declared = $plugin->extends()[$key] ?? [];
			if (!is_array($declared)) {
				throw new InvalidArgumentException(message: "The plugin {$plugin->name()}: {$key} must be an array of class names");
			}

			foreach ($declared as $type => $class) {
				if (!is_string($class) || !is_subclass_of($class, $baseClass)) {
					throw new InvalidArgumentException(message: "The plugin {$plugin->name()}: {$key} must contain classes that extend {$baseClass}");
				}

				$registered[is_string($type) ? $type : $class::type()] = $class;
			}
		}

		return array_replace($registered, $legacy);
	}

	/**
	 * Create the forms page if it doesn't exist yet
	 */
	public static function install(): void
	{
		$kirby = App::instance();
		$page = static::option('page');
		if ($kirby->page($page)?->exists()) {
			return;
		}

		$isUuid = Str::startsWith($page, "page://");

		// create the page
		$kirby->impersonate(
			'kirby',
			fn () => $kirby->site()->createChild([
				'slug' => $isUuid ? "forms" : $page,
				'template' => 'forms',
				'content' => [
					'uuid' => $isUuid ? Str::after($page, "page://") : 'forms',
				]
			])->changeStatus('unlisted')
		);
	}

	/**
	 * Find a page or draft recursively using the path
	 */
	public static function findPageOrDraftRecursive(string $path): Page|null
	{
		if (!$path) {
			return null;
		}

		$page = App::instance()->site();
		if (Str::startsWith($path, 'page://')) {
			return $page->findPageOrDraft($path);
		}

		$segments = Str::split($path, '/');
		foreach ($segments as $segment) {
			if ($page = $page->findPageOrDraft($segment)) {
				continue;
			}

			return null;
		}

		return $page;
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

		$page = static::findPageOrDraftRecursive(Str::replace($matches[1], '+', '/'));

		return $page;
	}

	/**
	 * Returns the debug mode option for the plugin
	 */
	public static function debugMode(): bool
	{
		return DreamForm::option('debug');
	}

	/**
	 * We need to normalize keys since Kirby does not support dashes in field names properly
	 */
	public static function normalizeKey(string $key): string
	{
		return Str::replace($key, '-', '_');
	}

	/**
	 * Returns the user agent string for the plugin
	 */
	public static function userAgent(): string
	{
		return "Kirby DreamForm/" . App::plugin('tobimori/dreamform')->version() . " (+https://plugins.andkindness.com/dreamform)";
	}

	/**
	 * Returns a plugin option
	 */
	public static function option(string $key, mixed $default = null): mixed
	{
		$option = App::instance()->option("tobimori.dreamform.{$key}", $default);
		if (is_callable($option)) {
			$option = $option();
		}

		return $option;
	}

	/**
	 * Simple request cache
	 */
	private static array $requestCache = [];

	/**
	 * Simple request cache
	 * https://github.com/bnomei/kirby3-lapse/blob/master/classes/LapseStatic.php
	 */
	public static function requestCache(string|array $key, Closure $closure)
	{
		if (is_array($key)) {
			$key = implode('-', $key);
		}

		if ($value = A::get(static::$requestCache, $key, null)) {
			return $value;
		}

		if (!is_string($closure) && is_callable($closure)) {
			static::$requestCache[$key] = $closure();
		}

		return static::$requestCache[$key];
	}
}
