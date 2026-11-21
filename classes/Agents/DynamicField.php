<?php

namespace tobimori\DreamForm\Agents;

use Kirby\Toolkit\Str;
use tobimori\Agents\Content\InputCheck;
use tobimori\Agents\Content\Presenter;
use tobimori\Agents\Fields\Field;
use tobimori\Agents\Schema\Compiler;

/**
 * Kirby Agents class for the `dreamform-dynamic-field` field.
 * Agents refer to form fields by key, the field stores their ID
 */
class DynamicField extends Field
{
	public function describe(Compiler $schema): string
	{
		$fields = array_map(
			fn (array $field) => self::quote($field['key']) . (empty($field['label']) ? '' : " ({$field['label']})"),
			$this->fields()
		);

		return '{"field": key} for the submitted value of a form field, or {"value": text} for a fixed value. Fields: '
			. ($fields === [] ? 'none yet' : implode(' | ', $fields));
	}

	public function input(mixed $value, mixed $current): mixed
	{
		$value = self::json($value);

		if ($value === null || $value === '' || $value === []) {
			return null;
		}

		if (is_scalar($value)) {
			$value = $this->find($value) ? ['field' => $value] : ['value' => (string)$value];
		}

		// keep other input as it is, check() rejects it
		if (!is_array($value)) {
			return $value;
		}

		$field = $value['field'] ?? null;
		if (($value['type'] ?? null) !== 'static' && $field !== null && $field !== '') {
			return ['type' => 'dynamic', 'field' => $this->find($field)['id'] ?? $field, 'value' => null];
		}

		if (array_key_exists('value', $value)) {
			return ['type' => 'static', 'field' => null, 'value' => $value['value']];
		}

		return $value;
	}

	public function check(mixed $value, InputCheck $check, string $where): void
	{
		if ($value === null || !$check->isNew($value)) {
			return;
		}

		$type = is_array($value) ? $value['type'] ?? null : null;
		if (
			!in_array($type, ['dynamic', 'static'], true) ||
			($type === 'static' && !is_string($value['value']) && $value['value'] !== null)
		) {
			$check->error("{$where}: send {\"field\": key} or {\"value\": text}");
			return;
		}

		if (($value['type'] ?? null) === 'dynamic' && !$this->find($value['field'] ?? null)) {
			$keys = array_map(fn (array $field) => "`{$field['key']}`", $this->fields());
			$check->error(
				"{$where}: the form has no field " . self::quote($value['field'] ?? null) . ' to use here. Fields: ' . ($keys === [] ? 'none' : implode(', ', $keys))
				. '. Fields that are added in the same call are not known yet, so add them first'
			);
		}
	}

	public function present(mixed $value, array $path, Presenter $presenter): mixed
	{
		$value = $this->stored($value);

		return match ($value['type'] ?? null) {
			'dynamic' => ['field' => $this->find($value['field'] ?? '')['key'] ?? $value['field'] ?? null],
			'static' => ['value' => $value['value'] ?? null],
			default => null
		};
	}

	/**
	 * Fields that the dynamic field can use, from its options
	 */
	protected function fields(): array
	{
		return array_values(array_filter($this->props['options'] ?? [], fn ($field) => is_array($field) && isset($field['id'], $field['key'])));
	}

	protected function find(mixed $ref): array|null
	{
		if (!is_string($ref)) {
			return null;
		}

		foreach ($this->fields() as $field) {
			if ($field['id'] === $ref || $field['key'] === str_replace('-', '_', $ref)) {
				return $field;
			}
		}

		return null;
	}

	/**
	 * Older forms store a list with the block UUID of the field or the fixed value,
	 * read like the `value` of the field in `config/fields.php`
	 */
	protected function stored(mixed $value): mixed
	{
		$value = self::json($value);

		if (is_array($value) && isset($value[0]) && is_string($value[0])) {
			return Str::match($value[0], '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-5][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}$/')
				? ['type' => 'dynamic', 'field' => $value[0]]
				: ['type' => 'static', 'value' => $value[0]];
		}

		return $value;
	}
}
