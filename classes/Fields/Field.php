<?php

namespace tobimori\DreamForm\Fields;

use Kirby\Cms\Block;

abstract class Field
{
	public Block $field;
	public static $type;

	public function ___construct(Block $field)
	{
		$this->field = $field;
	}

	abstract public function validate($value): true|string;

	public function sanitize($value)
	{
		return $value;
	}

	/** Returns the Blocks fieldset blueprint for the actions' settings */
	abstract public static function blueprint(): array;
}
