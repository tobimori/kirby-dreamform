<?php

namespace tobimori\DreamForm\Models\Log;

use Kirby\Cms\App;
use Kirby\Content\VersionId;
use Kirby\Data\Yaml;

/**
 * Add logging functionality to submission actions
 */
trait HasSubmissionLog
{
	public function log(): SubmissionLog
	{
		return SubmissionLog::factory(
			Yaml::decode($this->content()->get('dreamform_log')->value()),
			[
				'parent' => $this,
			]
		);
	}

	public function addLogEntry(array $data, ?string $type = null, ?string $icon = null, ?string $title = null): SubmissionLogEntry
	{
		$item = new SubmissionLogEntry([
			'parent' => $this,
			'siblings' => $items = $this->log(),
			'data' => $data,
			'type' => $type,
			'icon' => $icon,
			'title' => $title,
		]);

		$items = $items->add($item);
		App::instance()->impersonate('kirby', fn () => $this->version(VersionId::LATEST)->update([
			'dreamform_log' => Yaml::encode($items->toArray())
		]));

		return $item;
	}
}
