<?php

namespace tobimori\DreamForm\Actions\Log;

use DateTime;
use Kirby\Cms\Item;

/**
 * An action log entry
 */
class ActionLogEntry extends Item
{
	public const ITEMS_CLASS = ActionLog::class;

	protected int $timestamp;
	protected array $data;
	protected string $type;

	/**
	 * Creates a new ActionLogEntry with the given props
	 */
	public function __construct(array $params = [])
	{
		parent::__construct($params);

		$this->data = $params['data'] ?? [];
		$this->type = $params['type'] ?? 'info';
		$this->timestamp = $params['timestamp'] ?? time();
	}

	/**
	 * Returns the DateTime object for the log entry
	 */
	public function dateTime(): DateTime
	{
		return new DateTime($this->timestamp);
	}

	/**
	 * Returns the timestamp for the log entry
	 */
	public function timestamp(): int
	{
		return $this->timestamp;
	}

	/**
	 * Returns the type of the log entry
	 */
	public function type(): string
	{
		return $this->type;
	}

	/**
	 * Converts the item to an array
	 */
	public function toArray(): array
	{
		return [
			'data' => $this->data,
			'id' => $this->id(),
			'type' => $this->type(),
			'timestamp' => $this->timestamp(),
		];
	}
}
