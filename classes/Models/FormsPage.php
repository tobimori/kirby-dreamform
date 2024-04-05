<?php

namespace tobimori\DreamForm\Models;

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
