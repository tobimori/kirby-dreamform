<?php

namespace tobimori\DreamForm\Models\Log;

use DateTime;
use Kirby\Cms\Item;

/**
 * An action log entry
 */
class SubmissionLogEntry extends Item
{
	public const ITEMS_CLASS = SubmissionLog::class;

	protected int $timestamp;
	protected array $data;
	protected string $type;
	protected string $icon;
	protected string $title;

	/**
	 * Creates a new ActionLogEntry with the given props
	 */
	public function __construct(array $params = [])
	{
		parent::__construct($params);

		$this->data = $params['data'] ?? [];
		$this->type = $params['type'] ?? (empty($params['data']) ? 'none' : 'info');
		$this->icon = $params['icon'] ?? 'info';
		$this->title = $params['title'] ?? ucfirst($this->type);
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
	 * Returns the title of the log entry
	 */
	public function title(): string
	{
		return $this->title;
	}


	/**
	 * Returns the icon for the log entry
	 */
	public function icon(): string
	{
		return $this->icon;
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
			'icon' => $this->icon(),
			'title' => $this->title(),
			'timestamp' => $this->timestamp(),
		];
	}
}
