<?php

namespace tobimori\DreamForm\Panel;

use Kirby\Form\Field\ObjectField;
use Kirby\Reflection\Attributes\Prop;

/**
 * Object field that loads its fields from an external API
 */
class ApiObjectField extends ObjectField
{
	/**
	 * The Panel component loads the fields from the API. An exported
	 * empty list would override them, as it falls through to `k-object-field`
	 */
	#[Prop(export: false)]
	protected array $fields = [];

	/**
	 * Name of the sibling field that reloads the fields when it changes
	 */
	#[Prop]
	protected string|null $sync = null;
}
