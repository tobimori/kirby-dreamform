<?php

use Kirby\Content\VersionId;
use Kirby\Data\Data;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;
use tobimori\DreamForm\DreamForm;

return [
	'dreamform-api-object' => [
		'extends' => 'object',
		'props' => [
			// Unset inherited props
			'fields' => null,

			// reload when the following field changes
			'sync' => function (string|null $sync = null) {
				return $sync;
			},

			// fetch field setup from the API
			'api' => function (string|null $api = null) {
				return $api;
			}
		]
	],
	'dreamform-dynamic-field' => [
		'props' => [
			'after' => null,
			'before' => null,
			'placeholder' => null,
			'icon' => null,

			'limitType' => function (string|array|null $limitType = null): array|null {
				if (!is_array($limitType)) {
					$limitType = [$limitType];
				}

				$limitType = array_filter($limitType, fn ($type) => $type !== null);
				if (empty($limitType)) {
					return null;
				}

				return $limitType;
			}
		],
		'computed' => [
			'value' => function () {
				$data = Data::decode($this->value, 'yaml');

				if (isset($data[0]) && is_string($data[0])) {
					if (!!Str::match($data[0], "/^[0-9a-f]{8}-[0-9a-f]{4}-[0-5][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}$/")) {
						return [
							'type' => 'dynamic',
							'field' => $data[0],
							'value' => null
						];
					}

					return [
						'type' => 'static',
						'field' => null,
						'value' =>  $data[0]
					];
				}

				return $data;
			},
			'options'	=> function () {
				$page = $this->model;

				$limit = $this->limitType();
				return DreamForm::requestCache($limit ? [$page->uuid()->id(), implode(';', $limit)] : $page->uuid()->id(), function () use ($page, $limit) {
					$fields = [];
					foreach ($page->formFields(null, VersionId::CHANGES) as $field) {
						if (!$field::hasValue()) {
							continue;
						}

						if ($limit !== null && !A::has($limit, Str::replace($field->block()->type(), '-field', ''))) {
							continue;
						}

						$blueprint = $field::blueprint();

						$fields[] = [
							'id' => $field->id(),
							'label' => $field->block()->label()->or($field->key())->value(),
							'icon' => $blueprint['icon'] ?? 'input-cursor-move',
							'type' => $blueprint['name'] ?? "",
						];
					}

					return $fields;
				});
			}
		],
		'validations' => [
			'value' => function (array|null $value) {
				if (empty($value) === true && !$this->required()) {
					return true;
				}

				if ($value['type'] === 'dynamic' && $value['field']) {
					$limit = $this->limitType();
					foreach ($this->model->formFields() as $field) {
						if (!$field::hasValue()) {
							continue;
						}

						if ($limit !== null && !A::has($limit, Str::replace($field->block()->type(), '-field', ''))) {
							continue;
						}

						if ($field->id() === $value['field']) {
							return true;
						}
					}
				} elseif ($value['type'] === 'static' && $value['value']) {
					return true;
				}

				if ($this->required()) {
					throw new InvalidArgumentException();
				}
			},
		]
	],
	'dreamform-field-slug' => [
		'extends' => 'slug',
		'props' => [
			'allow' => function (string $allow = 'a-zA-Z0-9_') {
				return $allow;
			}
		]
	]
];
