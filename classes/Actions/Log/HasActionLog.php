<?php

namespace tobimori\Dreamform\Actions\Log;

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
			'data' => [
				'_type' => $type,
				'_timestamp' => date('c'),
				...$data
			]
		]);

		$items = $items->add($item);
		$this->update([
			'dreamform_log' => Yaml::encode($items->toArray())
		]);

		return $item;
	}
}
