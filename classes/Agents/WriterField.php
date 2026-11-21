<?php

namespace tobimori\DreamForm\Agents;

use tobimori\Agents\Content\InputCheck;
use tobimori\Agents\Content\Presenter;
use tobimori\Agents\Fields\Field;
use tobimori\Agents\Fields\WriterField as Writer;
use tobimori\Agents\Schema\Compiler;
use tobimori\DreamForm\DreamForm;

/**
 * Kirby Agents class for writer fields with field placeholders (`dreamform/fields/writer-with-fields`).
 * Agents write placeholders as `{{ key }}` text, the Panel stores them as nodes
 */
class WriterField extends Field
{
	private const NODE = '/<span\b(?=[^>]*\bdata-dreamform-field)[^>]*\bdata-field="([^"]*)"[^>]*>.*?<\/span>/s';

	public function describe(Compiler $schema): string
	{
		return $this->writer()->describe($schema) . ', {{ key }} inserts the submitted value of a form field';
	}

	public function input(mixed $value, mixed $current): mixed
	{
		$value = $this->writer()->input(is_string($value) ? self::text($value) : $value, $current);

		if (!is_string($value)) {
			return $value;
		}

		// only in text, not in tags and their attributes
		return preg_replace_callback(
			'/(<[^>]*>)|\{\{\s*([a-zA-Z0-9_-]+)\s*\}\}/',
			function (array $match) {
				if ($match[1] !== '') {
					return $match[1];
				}

				// submissions store values by the key with _ instead of -
				$key = DreamForm::normalizeKey($match[2]);
				return "<span data-dreamform-field=\"true\" data-field=\"{{ {$key} }}\">{{ {$key} }}</span>";
			},
			$value
		) ?? $value;
	}

	public function check(mixed $value, InputCheck $check, string $where): void
	{
		$this->writer()->check(is_string($value) ? self::text($value) : $value, $check, $where);
	}

	public function present(mixed $value, array $path, Presenter $presenter): mixed
	{
		return is_string($value) ? self::text($value) : $value;
	}

	public function prominent(): bool
	{
		return true;
	}

	// agents see the name of the class (`agents.as`) in outlines and errors
	protected function type(): string
	{
		return 'writer';
	}

	/**
	 * The core writer class, without the node for placeholders
	 */
	protected function writer(): Writer
	{
		$props = $this->props;

		if (is_array($props['nodes'] ?? null)) {
			$props['nodes'] = array_values(array_diff($props['nodes'], ['dreamformFormField']));
		}

		return new Writer($props);
	}

	/**
	 * Replaces the placeholder nodes with their `{{ key }}` text
	 */
	protected static function text(string $html): string
	{
		return preg_replace(self::NODE, '$1', $html) ?? $html;
	}
}
