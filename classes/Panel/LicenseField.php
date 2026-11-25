<?php

namespace tobimori\DreamForm\Panel;

use Kirby\Form\Field\DisplayField;
use tobimori\DreamForm\Support\License;

/**
 * Shows the license activation banner if the plugin is not activated
 */
class LicenseField extends DisplayField
{
	public function props(): array
	{
		return [
			...parent::props(),
			'activated' => License::fromDisk()->isValid(),
			'local' => $this->kirby()->system()->isLocal()
		];
	}
}
