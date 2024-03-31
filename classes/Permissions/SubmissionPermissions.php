<?php

namespace tobimori\DreamForm\Permissions;

use Kirby\Cms\PagePermissions;

class SubmissionPermissions extends PagePermissions
{
	protected function canAccess(): bool
	{
		return $this->permissions->for('tobimori.dreamform', 'accessSubmissions');
	}

	protected function canDelete(): bool
	{
		return $this->permissions->for('tobimori.dreamform', 'deleteSubmissions');
	}
}
