<?php

namespace tobimori\DreamForm\Permissions;

use Kirby\Guards\PagePermissions;

/**
 * Page permissions that also require a DreamForm role permission
 */
abstract class PluginPermissions extends PagePermissions
{
	/**
	 * Maps page actions to the DreamForm permission they need
	 *
	 * @var array<string, string>
	 */
	protected const array PERMISSIONS = [];

	/**
	 * Denies the action if the DreamForm permission is missing,
	 * otherwise the default page rules apply
	 */
	public function ensure(string $action, bool $default = false): void
	{
		$permission = static::PERMISSIONS[$action] ?? null;

		if (
			$permission !== null &&
			$this->user->role()->permissions()->for('tobimori.dreamform', $permission) !== true
		) {
			$this->error(match ($action) {
				'changeStatusToDraft', 'publish' => 'changeStatus',
				default => $action
			});
		}

		parent::ensure($action, $default);
	}
}
