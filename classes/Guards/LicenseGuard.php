<?php

namespace tobimori\DreamForm\Guards;

use Kirby\Cms\App;
use tobimori\DreamForm\Support\License;

class LicenseGuard extends Guard
{
	public const TYPE = 'license';

	public function precognitiveRun(): void
	{
		$license = License::fromDisk();
		if (!$license->isValid() && !App::instance()->system()->isLocal() && !App::instance()->user()?->isAdmin()) {
			$this->cancel(t('dreamform.license.error.submission'), public: true);
		}
	}
}
