<?php

namespace tobimori\DreamForm\Fields;

use Kirby\Cms\Block;
use Kirby\Content\Field as ContentField;
use Kirby\Exception\Exception;

abstract class Field
{
	protected Block $field;
	protected ContentField|null $content;

	public function __construct(Block $field, ContentField|null $content = null)
	{
		$this->field = $field;
		$this->content = $content;
	}

	public function field(): Block
	{
		return $this->field;
	}

	public function content(): ContentField
	{
		if (!$this->content) {
			return new Exception('Field content is not set');
		}

		return $this->content;
	}

	/** Returns true or an error message for the user frontend */
	public function validate(): true|string
	{
		return true;
	}

	/** Returns the sanitzed content of the field */
	public function sanitize(): mixed
	{
		return $this->content()->value();
	}

	public function setContent(ContentField $content)
	{
		$this->content = $content;
	}

	/** Returns the Blocks fieldset blueprint for the actions' settings */
	abstract public static function blueprint(): array;
}
