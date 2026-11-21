<?php

namespace tobimori\DreamForm\Agents;

use tobimori\Agents\Content\InputCheck;
use tobimori\Agents\Fields\LayoutField;

/**
 * Kirby Agents class for the layout with the fields of a form
 */
class FieldsField extends LayoutField
{
	public function check(mixed $value, InputCheck $check, string $where): void
	{
		parent::check($value, $check, $where);

		$keys = [];
		foreach (is_array($value) ? $value : [] as $row) {
			foreach (is_array($row['columns'] ?? null) ? $row['columns'] : [] as $column) {
				$blocks = is_array($column['blocks'] ?? null) ? $column['blocks'] : [];

				if (($column['width'] ?? null) === 'dreamform-page' && $blocks !== [] && $check->isNew($column)) {
					$check->error("{$where}: the `dreamform-page` row only starts the next step, so it cannot have blocks. Insert the fields of the step in new rows after it");
				}

				foreach ($blocks as $block) {
					$key = is_array($block) ? $block['content']['key'] ?? null : null;
					if (is_string($key) && $key !== '') {
						$keys[] = strtolower(str_replace('-', '_', $key));
					}
				}
			}
		}

		// forms can have duplicates already, they must not block other changes
		$duplicates = array_filter(
			array_unique(array_diff_assoc($keys, array_unique($keys))),
			fn (string $key) => $check->isNew("duplicate key {$key}")
		);
		if ($duplicates !== []) {
			$check->error("{$where}: each field needs its own key, `" . implode('`, `', $duplicates) . '` is used more than once');
		}
	}

	// agents see the name of the class (`agents.as`) in outlines and errors
	protected function type(): string
	{
		return 'layout';
	}
}
