<?php

namespace tobimori\DreamForm\Permissions;

use Kirby\Cms\PagePermissions;

class SubmissionPermissions extends PagePermissions
{
	protected function canAccess(): bool
	{
		return static::user()->role()->permissions()->for('tobimori.dreamform', 'accessSubmissions');
	}

	protected function canDelete(): bool
	{
		return static::user()->role()->permissions()->for('tobimori.dreamform', 'deleteSubmissions');
	}
}
