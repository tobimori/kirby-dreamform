<?php

namespace tobimori\DreamForm\Fields;

abstract class Field
{
	public static $type;

	/** Returns the Blocks fieldset blueprint for the actions' settings */
	abstract public static function blueprint(): array;
}
