<?php

namespace tobimori\DreamForm\Permissions;

use Kirby\Cms\Page;
use Kirby\Cms\User;
use Kirby\Guards\PageGuards;

/**
 * Page guards with the DreamForm role permissions for submission pages
 */
class SubmissionGuards extends PageGuards
{
	public function __construct(Page $model, User $user)
	{
		parent::__construct(model: $model, user: $user);
		$this->permissions = new SubmissionPermissions(model: $model, user: $user);
	}
}
