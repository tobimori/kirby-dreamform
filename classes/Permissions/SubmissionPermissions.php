<?php

namespace tobimori\DreamForm\Permissions;

/**
 * Applies the DreamForm role permissions to submission pages
 */
class SubmissionPermissions extends PluginPermissions
{
	protected const array PERMISSIONS = [
		'access' => 'accessSubmissions',
		'delete' => 'deleteSubmissions',
	];
}
