<?php

namespace tobimori\DreamForm\Fields;

use Kirby\Cms\Block;
use Kirby\Content\Field as ContentField;
use Kirby\Exception\Exception;

abstract class Field
{
	protected string $id;
	protected Block $field;
	protected ContentField|null $value;

	public function __construct(Block $field, ContentField|null $value = null)
	{
		$this->id = $field->id();
		$this->field = $field;
		$this->value = $value;
	}

	public function id(): string
	{
		return $this->id;
	}

	public function field(): Block
	{
		return $this->field;
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

	/** Returns the sanitzed value of the field */
	public function sanitize(): mixed
	{
		return $this->value()->value();
	}

	public function setValue(ContentField $value)
	{
		$this->value = $value;
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
