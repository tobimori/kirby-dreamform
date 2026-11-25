<?php

namespace tobimori\DreamForm\Permissions;

use Kirby\Cms\Page;
use Kirby\Cms\User;
use Kirby\Guards\PageGuards;

/**
 * Page guards with the DreamForm role permissions for form pages
 */
class FormGuards extends PageGuards
{
	public function __construct(Page $model, User $user)
	{
		parent::__construct(model: $model, user: $user);
		$this->permissions = new FormPermissions(model: $model, user: $user);
	}
}
