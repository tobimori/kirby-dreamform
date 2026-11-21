<?php

namespace tobimori\DreamForm\Agents;

use tobimori\Agents\Fields\Field;
use tobimori\Agents\Schema\Compiler;
use tobimori\Agents\Tools\ToolError;

/**
 * Kirby Agents class for the `dreamform-api-object` field. Its fields come from
 * an external API in the Panel, so agents cannot change it
 */
class ApiObjectField extends Field
{
	public function describe(Compiler $schema): string
	{
		return 'object, read-only: an editor sets it in the Panel';
	}

	public function input(mixed $value, mixed $current): mixed
	{
		$value = self::json($value);

		// agents see the value and can send it back unchanged
		if ($value !== $current && $value !== null && $value !== []) {
			throw new ToolError('This field is read-only: an editor must set it in the Panel');
		}

		return $current;
	}

	// a new value for the whole blocks field would remove it
	public function locks(array $value): bool
	{
		return $value !== [];
	}
}
