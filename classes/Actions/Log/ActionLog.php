<?php

namespace tobimori\DreamForm\Actions\Log;

use Closure;
use Kirby\Cms\Items;
use Kirby\Toolkit\A;

/**
 * The action log for a submission
 */
class ActionLog extends Items
{
	public const ITEM_CLASS = ActionLogEntry::class;

	/**
	 * Convert the items to an array
	 */
	public function toArray(Closure $map = null): array
	{
		return A::sort(
			parent::toArray($map),
			'timestamp',
			'asc',
			SORT_NUMERIC
		);
	}
}
