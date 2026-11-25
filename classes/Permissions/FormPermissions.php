<?php

namespace tobimori\DreamForm\Permissions;

/**
 * Applies the DreamForm role permissions to form pages
 */
class FormPermissions extends PluginPermissions
{
	protected const array PERMISSIONS = [
		'access' => 'accessForms',
		'create' => 'createForms',
		'update' => 'updateForms',
		'delete' => 'deleteForms',
		'duplicate' => 'duplicateForms',
		'changeTitle' => 'changeFormTitle',
		'changeStatus' => 'changeFormStatus',
		'changeStatusToDraft' => 'changeFormStatus',
		'publish' => 'changeFormStatus',
		'changeSlug' => 'changeFormSlug',
	];
}
