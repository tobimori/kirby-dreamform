<?php

namespace tobimori\DreamForm\Actions\Log;

use Kirby\Data\Yaml;

/**
 * Add logging functionality to submission actions
 */
trait HasActionLog
{
	public function actionLog(): ActionLog
	{
		return ActionLog::factory(
			Yaml::decode($this->content()->get('dreamform_log')->value()),
			[
				'parent' => $this,
			]
		);
	}

	public function logAction(string $type, array $data): ActionLogEntry
	{
		$item = new ActionLogEntry([
			'parent' => $this,
			'siblings' => $items = $this->actionLog(),
			'type' => $type,
			'data' => $data
		]);

		$items = $items->add($item);
		$this->update([
			'dreamform_log' => Yaml::encode($items->toArray())
		]);

		return $item;
	}
}
