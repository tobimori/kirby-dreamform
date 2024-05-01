<?php

namespace tobimori\DreamForm\Models\Log;

use Closure;
use Kirby\Cms\Items;
use Kirby\Toolkit\A;

/**
 * The action log for a submission
 */
class SubmissionLog extends Items
{
	public const ITEM_CLASS = SubmissionLogEntry::class;

	/**
	 * Convert the items to an array
	 */
	public function toArray(Closure $map = null): array
	{
		return array_values(A::sort(
			parent::toArray($map),
			'timestamp',
			'desc',
			SORT_NUMERIC
		));
	}
}
