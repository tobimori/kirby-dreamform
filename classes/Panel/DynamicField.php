<?php

namespace tobimori\DreamForm\Panel;

use Kirby\Content\VersionId;
use Kirby\Data\Data;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Form\Field\InputField;
use Kirby\Reflection\Attributes\Prop;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;
use tobimori\DreamForm\DreamForm;

/**
 * Field that takes a static value or the value of a form field
 */
class DynamicField extends InputField
{
	/**
	 * Limits the selectable form fields to the given field types
	 */
	#[Prop]
	protected array|string|null $limitType = null;

	protected array $value = [];

	/**
	 * Converts the legacy list format to the current format
	 */
	public function fill(mixed $value): static
	{
		$data = Data::decode($value, 'yaml');

		if (isset($data[0]) && is_string($data[0])) {
			if (Str::match($data[0], "/^[0-9a-f]{8}-[0-9a-f]{4}-[0-5][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}$/")) {
				$data = [
					'type' => 'dynamic',
					'field' => $data[0],
					'value' => null
				];
			} else {
				$data = [
					'type' => 'static',
					'field' => null,
					'value' => $data[0]
				];
			}
		}

		return parent::fill($data);
	}

	public function limitType(): array|null
	{
		$limitType = array_filter(A::wrap($this->limitType), fn ($type) => $type !== null);

		return $limitType === [] ? null : $limitType;
	}

	/**
	 * Returns the form fields that can be selected
	 */
	public function options(): array
	{
		$page = $this->model();

		// when creating a page, it doesn't exist yet
		// accessing uuid()->id() below would trigger uuid generation,
		// which writes to disk and creates the page directory prematurely
		if (!$page?->exists()) {
			return [];
		}

		$limit = $this->limitType();
		return DreamForm::requestCache($limit ? [$page->uuid()->id(), implode(';', $limit)] : $page->uuid()->id(), function () use ($page) {
			$fields = [];
			foreach ($this->selectableFields($page->formFields(null, VersionId::CHANGES)) as $field) {
				$blueprint = $field::blueprint();

				$fields[] = [
					'id' => $field->id(),
					'key' => $field->key(),
					'label' => $field->block()->label()->or($field->key())->value(),
					'icon' => $blueprint['icon'] ?? 'input-cursor-move',
					'type' => $blueprint['name'] ?? "",
				];
			}

			return $fields;
		});
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'options' => $this->options()
		];
	}

	/**
	 * Filters the given form fields by value and type limit
	 */
	protected function selectableFields(iterable $fields): array
	{
		$limit = $this->limitType();
		$result = [];

		foreach ($fields as $field) {
			if (!$field::hasValue()) {
				continue;
			}

			if ($limit !== null && !A::has($limit, Str::replace($field->block()->type(), '-field', ''))) {
				continue;
			}

			$result[] = $field;
		}

		return $result;
	}

	protected function validations(): array
	{
		return [
			'value' => function (array $value) {
				if ($value === [] && !$this->isRequired()) {
					return;
				}

				if (($value['type'] ?? null) === 'dynamic' && ($value['field'] ?? null)) {
					foreach ($this->selectableFields($this->model()->formFields(null, VersionId::CHANGES)) as $field) {
						if ($field->id() === $value['field']) {
							return;
						}
					}
				} elseif (($value['type'] ?? null) === 'static' && ($value['value'] ?? null)) {
					return;
				}

				if ($this->isRequired()) {
					throw new InvalidArgumentException();
				}
			}
		];
	}
}
