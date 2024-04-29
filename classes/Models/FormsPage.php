<?php

namespace tobimori\DreamForm\Models;

/**
 * The forms page is the directory for all forms.
 */
class FormsPage extends BasePage
{
	/**
	 * Removes the children from the Link field
	 */
	public function hasChildren(): bool
	{
		return false;
	}
}
