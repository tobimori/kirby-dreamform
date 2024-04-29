<?php

namespace tobimori\DreamForm\Actions\Log;

use Kirby\Cms\Items;

/**
 * The action log for a submission
 */
class ActionLog extends Items
{
	public const ITEM_CLASS = ActionLogEntry::class;
}
