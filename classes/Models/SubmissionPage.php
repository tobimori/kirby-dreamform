<?php

namespace tobimori\DreamForm\Models;

use Kirby\Cms\Collection;
use Kirby\Cms\Page;
use Kirby\Exception\Exception;

class SubmissionPage extends BasePage
{
	public static $registeredFields = [];
	public static $registeredActions = [];
	private Collection $fields;
	private Page|null $referer = null;

	public function __construct(array $props)
	{
		parent::__construct($props);

		ray($props);
		if (isset($props['referer'])) {
			$this->referer = $props['referer'];
		}

		if (!isset($props['fields'])) {
			throw new Exception('No fields found in submission page');
		}

		$this->fields = $props['fields'];
	}

	public function fields(): Collection
	{
		return $this->fields;
	}

	public function referer(): Page|null
	{
		return $this->referer;
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
