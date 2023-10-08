<?php

namespace tobimori\DreamForm\Models;

use Kirby\Cms\Collection;
use Kirby\Exception\Exception;

class SubmissionPage extends BasePage
{
	public static $registeredFields = [];
	public static $registeredActions = [];
	private Collection $fields;

	public function __construct(array $props)
	{
		parent::__construct($props);

		if (!isset($props['fields'])) {
			throw new Exception('No fields found in submission page');
		}

		$this->fields = $props['fields'];
	}

	public function fields(): Collection
	{
		return $this->fields;
	}

	public function fieldValues(): array
	{
		$fieldValues = [];
		foreach ($this->fields() as $field) {
			$fieldValues[$field->field()->key()->or($field->id())->value()] = $field->value();
		}

		return $fieldValues;
	}
}
