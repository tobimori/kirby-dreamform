<?php

use tobimori\DreamForm\Panel\ApiObjectField;
use tobimori\DreamForm\Panel\DynamicField;
use tobimori\DreamForm\Panel\FieldSlugField;
use tobimori\DreamForm\Panel\LicenseField;
use tobimori\DreamForm\Panel\SubmissionField;

return [
	'dreamform-api-object' => ApiObjectField::class,
	'dreamform-dynamic-field' => DynamicField::class,
	'dreamform-field-slug' => FieldSlugField::class,
	'dreamform-license' => LicenseField::class,
	'dreamform-submission' => SubmissionField::class,
];
