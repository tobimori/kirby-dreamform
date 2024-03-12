<?php

namespace tobimori\DreamForm\Fields;

use Kirby\Cms\App;
use Kirby\Cms\Block;
use Kirby\Content\Field as ContentField;
use Kirby\Exception\Exception;
use Kirby\Toolkit\Str;

abstract class Field
{
	protected string $id;
	protected Block $block;
	protected ContentField|null $value;

	public function __construct(Block $block, ContentField|null $value = null)
	{
		$this->id = $block->id();
		$this->block = $block;
		$this->value = $value;
	}

	/** Returns the fields' ID */
	public function id(): string
	{
		return $this->id;
	}

	/** Returns the fields' key or ID as fallback */
	public function key(): string
	{
		return Str::replace($this->block()->key()->or($this->id())->value(), '-', '_');
	}

	/** Returns the fields' block, which stores configuration of the instance */
	public function block(): Block
	{
		return $this->block;
	}

	public function value(): ContentField
	{
		if (!$this->value) {
			return new Exception('Field value is not set');
		}

		return $this->value;
	}

	/** Returns true or an error message for the user frontend */
	public function validate(): true|string
	{
		return true;
	}

	// TODO: figure out whether we need to run sanitization by default
	/** Returns the sanitzed value of the field */
	protected function sanitize(ContentField $value): ContentField
	{
		return $value;
	}

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

	/** Returns the values fieldset blueprint for the fields' settings */
	abstract public static function blueprint(): array;

	public function submissionBlueprint(): array|null
	{
		return null;
	}
}
