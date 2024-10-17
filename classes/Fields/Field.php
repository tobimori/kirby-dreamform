<?php

namespace tobimori\DreamForm\Fields;

use Kirby\Cache\Cache;
use Kirby\Cms\App;
use Kirby\Cms\Block;
use Kirby\Content\Field as ContentField;
use Kirby\Exception\Exception;
use Kirby\Toolkit\Str;
use tobimori\DreamForm\DreamForm;
use tobimori\DreamForm\Models\FormPage;
use tobimori\DreamForm\Models\SubmissionPage;
use tobimori\DreamForm\Support\HasCache;
use tobimori\DreamForm\Support\Htmx;

/**
 * Base class for all fields
 */
abstract class Field
{
	use HasCache;

	private string $id;

	/**
	 * Create a new Field instance from the corresponding block instance
	 */
	public function __construct(protected Block $block, protected ContentField|null $value = null)
	{
		$this->id = $block->id();
	}

	/**
	 * Returns the fields' ID
	 */
	public function id(): string
	{
		return $this->id;
	}

	/**
	 * Returns the fields' key or ID as fallback
	 */
	public function key(): string
	{
		return Str::replace($this->block()->key()->or($this->id())->value(), '-', '_');
	}

	/**
	 * Returns the fields' block, which stores configuration of the instance
	 */
	public function block(): Block
	{
		return $this->block;
	}

	/**
	 * Returns the fields' error message from the block content
	 */
	public function errorMessage(string $key = 'errorMessage'): string
	{
		return $this->block()->{$key}()->isNotEmpty() ? $this->block()->{$key}() : t('dreamform.fields.error.required');
	}

	/**
	 * Returns the fields' value
	 */
	public function value(): ContentField
	{
		if (!$this->value) {
			return new ContentField($this->block()->parent(), $this->key(), null);
		}

		return $this->value;
	}

	/**
	 * Returns the fields' label or key as fallback
	 */
	public function label(): string
	{
		return $this->block()->label()->value() ?? $this->key();
	}

	/**
	 * Validate the field value
	 * Returns true or an error message for the user frontend
	 */
	public function validate(): true|string
	{
		return true;
	}

	/**
	 * Run logic after the form submission
	 * e.g. for storing an uploaded file
	 */
	public function afterSubmit(SubmissionPage $submission): void
	{
	}

	/**
	 * Returns the sanitzed value of the field
	 */
	protected function sanitize(ContentField $value): ContentField
	{
		return $value;
	}

	/**
	 * Set the fields' value
	 */
	public function setValue(ContentField $value): static
	{
		$this->value = $this->sanitize($value);
		return $this;
	}

	/**
	 * Returns true if the field is able to have/store a value
	 * Set it to false, if your component is a field without user input,
	 * like a headline or a separator
	 */
	public static function hasValue(): bool
	{
		return true;
	}

	/**
	 * Returns the values fieldset blueprint for the fields' settings
	 */
	abstract public static function blueprint(): array;

	/**
	 * Returns the fields' submission blueprint
	 */
	public function submissionBlueprint(): array|null
	{
		return null;
	}

	public function htmxAttr(FormPage $form): array
	{
		if (!Htmx::isActive() || !DreamForm::option('precognition')) {
			return [];
		}

		$htmx = [
			'hx-post' => $form->url(precognition: true),

			// we want to show the error in the last moment (on blur)
			// but we want to remove the error as soon as possible (on change)
			'hx-trigger' => $this->validate() === true ? "change" : "change, input changed delay:500ms",

			// for on change validation we need the morph extension (otherwise we lose focus)
			'hx-swap' => 'morph:{ignoreActiveValue:true}',
			'hx-ext' => 'morph'
		];

		return $htmx;
	}

	/**
	 * Returns the fields' type
	 */
	public static function type(): string
	{
		return Str::kebab(Str::match(static::class, "/Fields\\\([a-zA-Z]+)Field/")[1]);
	}

	/**
	 * Returns the fields' blueprint group
	 */
	public static function group(): string
	{
		return 'common';
	}

	/**
	 * Returns true if the field is available
	 *
	 * Use this to disable fields based on configuration or other factors
	 */
	public static function isAvailable(FormPage|null $form = null): bool
	{
		return true;
	}

	/**
	 * Get the fields's cache instance
	 */
	private static function cacheInstance(): Cache
	{
		return App::instance()->cache('tobimori.dreamform.fields');
	}
}
