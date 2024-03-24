<?php

namespace tobimori\DreamForm\Models;

class FormsPage extends BasePage
{
	/**
	 * Removes the children from the Link field
	 * TODO: check if this makes anything stop working
	 */
	public function hasChildren(): bool
	{
		return false;
	}
}
