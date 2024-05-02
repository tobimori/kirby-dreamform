<?php

namespace tobimori\DreamForm\Actions;

/**
 * Action for conditionally running other actions.
 */
class ConditionalAction extends Action
{
	public static function blueprint(): array
	{
		return [
			'name' => t('dreamform.actions.conditional.name'),
			'preview' => 'fields',
			'wysiwyg' => true,
			'icon' => 'split',
			'tabs' => [
				'conditions' => [
					'label' => t('dreamform.actions.conditional.conditions.label'),
					'fields' => [
						'conditions' => [
							'label' => t('dreamform.actions.conditional.conditions.label'),
							'type' => 'structure',
							'fields' => [
								'field' => [
									'label' => 'dreamform.actions.conditional.if.label',
									'extends' => 'dreamform/fields/field',
									'required' => true,
									'width' => '1/3'
								],
								'operator' => [
									'label' => 'dreamform.actions.conditional.operator.label',
									'type' => 'select',
									'options' => [
										'equals' => t('dreamform.actions.conditional.operator.equals'),
										'not-equals' => t('dreamform.actions.conditional.operator.notEquals'),
										'contains' => t('dreamform.actions.conditional.operator.contains'),
										'not-contains' => t('dreamform.actions.conditional.operator.notContains'),
										'starts-with' => t('dreamform.actions.conditional.operator.startsWith'),
										'ends-with' => t('dreamform.actions.conditional.operator.endsWith'),
									],
									'required' => true,
									'width' => '1/6'
								],
								'value' => [
									'label' => 'dreamform.actions.conditional.value.label',
									'type' => 'text',
									'width' => '3/6'
								]
							]
						],
						'thatActions' => [
							'label' => 'dreamform.actions.conditional.thatActions.label',
							'extends' => 'dreamform/fields/actions',
							'fieldsets' => [
								static::group() => [
									'fieldsets' => [
										'conditional-action' => false // prevent infinite recursion
									]
								]
							]
						],
						'elseActions' => [
							'label' => 'dreamform.actions.conditional.elseActions.label',
							'extends' => 'dreamform/fields/actions',
							'fieldsets' => [
								static::group() => [
									'fieldsets' => [
										'conditional-action' => false // prevent infinite recursion
									]
								]
							]
						]
					]
				]
			]
		];
	}

	public function conditionsMet(): bool
	{
		foreach ($this->block()->conditions()->toStructure() as $condition) {
			$submitted = $this->submission()->valueForId($condition->content()->get('field')->value())?->value();
			$expected = $condition->value()->value();

			switch ($condition->operator()->value()) {
				case 'equals':
					if ($submitted !== $expected) {
						return false;
					}
					break;
				case 'not-equals':
					if ($submitted === $expected) {
						return false;
					}
					break;
				case 'contains':
					if (strpos($submitted, $expected) === false) {
						return false;
					}
					break;
				case 'not-contains':
					if (strpos($submitted, $expected) !== false) {
						return false;
					}
					break;
				case 'starts-with':
					if (strpos($submitted, $expected) !== 0) {
						return false;
					}
					break;
				case 'ends-with':
					if (substr($submitted, -strlen($expected)) !== $expected) {
						return false;
					}
					break;
			}
		}

		return true;
	}

	public function run(): void
	{
		$collection = $this->conditionsMet() ? $this->block()->thatActions() : $this->block()->elseActions();
		foreach ($this->submission()->createActions($collection->toBlocks()) as $action) {
			$action->run();
		}
	}
}
