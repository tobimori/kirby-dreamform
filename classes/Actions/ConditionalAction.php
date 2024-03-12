<?php

namespace tobimori\DreamForm\Actions;

use Kirby\Toolkit\Str;

/**
 * Action for conditionally running other actions.
 * @package tobimori\DreamForm\Actions
 */
class ConditionalAction extends Action
{
	public static function blueprint(): array
	{
		return [
			'title' => t('dreamform.conditional-action'),
			'preview' => 'fields',
			'wysiwyg' => true,
			'icon' => 'split',
			'tabs' => [
				'conditions' => [
					'label' => t('dreamform.conditions'),
					'fields' => [
						'conditions' => [
							'label' => t('dreamform.conditions'),
							'type' => 'structure',
							'fields' => [
								'field' => [
									'label' => 'dreamform.if-field',
									'extends' => 'dreamform/fields/field',
									'required' => true,
									'width' => '1/3'
								],
								'operator' => [
									'label' => 'dreamform.is-operator',
									'type' => 'select',
									'options' => [
										'equals' => t('dreamform.equals'),
										'not-equals' => t('dreamform.not-equals'),
										'contains' => t('dreamform.contains'),
										'not-contains' => t('dreamform.not-contains'),
										'starts-with' => t('dreamform.starts-with'),
										'ends-with' => t('dreamform.ends-with'),
									],
									'required' => true,
									'width' => '1/6'
								],
								'value' => [
									'label' => 'dreamform.is-value',
									'type' => 'text',
									'width' => '3/6'
								]
							]
						],
						'thatActions' => [
							'label' => 'dreamform.that-actions',
							'extends' => 'dreamform/fields/actions',
							'fieldsets' => [
								'conditional-action' => false // prevent infinite recursion
							]
						],
						'elseActions' => [
							'label' => 'dreamform.else-actions',
							'extends' => 'dreamform/fields/actions',
							'fieldsets' => [
								'conditional-action' => false // prevent infinite recursion
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

	public function run(): array|null
	{
		$data = null;
		$collection = $this->conditionsMet() ? $this->block()->thatActions() : $this->block()->elseActions();
		foreach ($this->submission()->createActions($collection->toBlocks()) as $action) {
			$actionData = $action->run();

			if ($actionData) {
				$data ??= ['actions' => []];
				$data['actions'][] = [
					'type' => Str::replace($action->action()->type(), '-action', ''),
					...$actionData
				];
			}
		}

		return $data;
	}
}
