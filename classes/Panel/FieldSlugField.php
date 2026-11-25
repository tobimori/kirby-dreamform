<?php

namespace tobimori\DreamForm\Panel;

use Kirby\Form\Field\SlugField;

/**
 * Slug field for form field keys, which also allows underscores
 */
class FieldSlugField extends SlugField
{
	protected string|null $allow = 'a-zA-Z0-9_';

	// an icon would cut off the input too early
	protected string|null $icon = null;
}
