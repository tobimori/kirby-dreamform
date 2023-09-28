<?php

namespace tobimori\DreamForm\Fields;

class ButtonField extends Field
{
	public static $type = 'button';

	public static function blueprint(): array
	{
		return [
			'title' => t('button-field'),
			'icon' => 'ticket',
		];
	}

	public function validate($value): true|string
	{
		return true;
	}
}
