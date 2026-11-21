<?php

namespace tobimori\DreamForm\Agents;

use Kirby\Toolkit\Str;
use tobimori\Agents\Content\InputCheck;
use tobimori\Agents\Fields\TextField;
use tobimori\Agents\Schema\Compiler;

/**
 * Kirby Agents class for the key of form fields (`dreamform-field-slug`)
 */
class KeyField extends TextField
{
	public function describe(Compiler $schema): string
	{
		return 'field key with letters, digits and _, unique in the form';
	}

	public function check(mixed $value, InputCheck $check, string $where): void
	{
		if (!is_string($value) || $value === '' || !$check->isNew($value)) {
			return;
		}

		if (!preg_match('/^[a-zA-Z0-9_-]+$/', $value)) {
			$check->error("{$where}: use only letters, digits and _, like `" . Str::slug($value, '_') . '`');
		}

		$key = strtolower($value);
		if (str_starts_with($key, 'dreamform') || in_array($key, ['title', 'slug', 'uuid'], true)) {
			$check->error("{$where}: `{$value}` is reserved, use another key");
		}
	}
}
