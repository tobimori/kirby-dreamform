<?php

namespace tobimori\DreamForm\Permissions;

use Kirby\Cms\PagePermissions;

class FormPermissions extends PagePermissions
{
	protected function canAccess(): bool
	{
		return static::user()->role()->permissions()->for('tobimori.dreamform', 'accessForms');
	}

	protected function canCreate(): bool
	{
		return static::user()->role()->permissions()->for('tobimori.dreamform', 'createForms');
	}

	protected function canUpdate(): bool
	{
		return static::user()->role()->permissions()->for('tobimori.dreamform', 'updateForms');
	}

	protected function canDelete(): bool
	{
		return static::user()->role()->permissions()->for('tobimori.dreamform', 'deleteForms');
	}

	protected function canDuplicate(): bool
	{
		return static::user()->role()->permissions()->for('tobimori.dreamform', 'duplicateForms');
	}

	protected function canChangeTitle(): bool
	{
		return static::user()->role()->permissions()->for('tobimori.dreamform', 'changeFormTitle');
	}

	protected function canChangeStatus(): bool
	{
		return static::user()->role()->permissions()->for('tobimori.dreamform', 'changeFormStatus');
	}

	protected function canChangeSlug(): bool
	{
		return static::user()->role()->permissions()->for('tobimori.dreamform', 'changeFormSlug');
	}
}
