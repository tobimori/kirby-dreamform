<?php

namespace tobimori\DreamForm\Permissions;

use Kirby\Cms\PagePermissions;

class FormPermissions extends PagePermissions
{
	protected function canAccess(): bool
	{
		return $this->permissions->for('tobimori.dreamform', 'accessForms');
	}

	protected function canCreate(): bool
	{
		return $this->permissions->for('tobimori.dreamform', 'createForms');
	}

	protected function canUpdate(): bool
	{
		return $this->permissions->for('tobimori.dreamform', 'updateForms');
	}

	protected function canDelete(): bool
	{
		return $this->permissions->for('tobimori.dreamform', 'deleteForms');
	}

	protected function canDuplicate(): bool
	{
		return $this->permissions->for('tobimori.dreamform', 'duplicateForms');
	}

	protected function canChangeTitle(): bool
	{
		return $this->permissions->for('tobimori.dreamform', 'changeFormTitle');
	}

	protected function canChangeStatus(): bool
	{
		return $this->permissions->for('tobimori.dreamform', 'changeFormStatus');
	}

	protected function canChangeSlug(): bool
	{
		return $this->permissions->for('tobimori.dreamform', 'changeFormSlug');
	}
}
