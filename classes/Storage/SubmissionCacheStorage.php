<?php

namespace tobimori\DreamForm\Storage;

use Kirby\Cache\Cache;
use Kirby\Cms\App;
use Kirby\Cms\Language;
use Kirby\Cms\ModelWithContent;
use Kirby\Content\Storage;
use Kirby\Content\VersionId;
use tobimori\DreamForm\Models\SubmissionPage;

/**
 * Storage handler that uses Kirby cache for sessionless storage
 */
class SubmissionCacheStorage extends Storage
{
	/**
	 * Cache instance
	 */
	protected Cache $cache;

	/**
	 * Sets up the cache instance
	 */
	public function __construct(protected ModelWithContent $model)
	{
		parent::__construct($model);
		$this->cache = App::instance()->cache('tobimori.dreamform.sessionless');
	}

	/**
	 * Returns the cache key for storing submission data
	 */
	protected function cacheKey(): string
	{
		return $this->model->id() . ':data';
	}

	/**
	 * Get submission data from cache
	 */
	protected function getCacheData(): array
	{
		return $this->cache->get($this->cacheKey()) ?? [];
	}

	/**
	 * Set submission data in cache
	 */
	protected function setCacheData(array $data): void
	{
		$this->cache->set($this->cacheKey(), $data, 60 * 24); // 24 hours
	}

	/**
	 * Deletes an existing version in an idempotent way
	 */
	public function delete(VersionId $versionId, Language $language): void
	{
		$data = $this->getCacheData();
		unset($data[$versionId->value()][$language->code()]);
		$this->setCacheData($data);
	}

	/**
	 * Checks if a version exists
	 */
	public function exists(VersionId $versionId, Language $language): bool
	{
		$data = $this->getCacheData();
		return isset($data[$versionId->value()][$language->code()]);
	}

	/**
	 * Returns the modification timestamp of a version if it exists
	 */
	public function modified(VersionId $versionId, Language $language): int|null
	{
		$data = $this->getCacheData();
		return $data[$versionId->value()][$language->code()]['_modified'] ?? null;
	}

	/**
	 * Returns the stored content fields
	 *
	 * @return array<string, string>
	 */
	public function read(VersionId $versionId, Language $language): array
	{
		$data = $this->getCacheData();
		$fields = $data[$versionId->value()][$language->code()] ?? [];
		unset($fields['_modified']);
		return $fields;
	}

	/**
	 * Updates the modification timestamp of an existing version
	 *
	 * @throws \Kirby\Exception\NotFoundException If the version does not exist
	 */
	public function touch(VersionId $versionId, Language $language): void
	{
		if (!$this->exists($versionId, $language)) {
			throw new \Kirby\Exception\NotFoundException('Version does not exist');
		}

		$data = $this->getCacheData();
		$data[$versionId->value()][$language->code()]['_modified'] = time();
		$this->setCacheData($data);
	}

	/**
	 * Writes the content fields of an existing version
	 *
	 * @param array<string, string> $fields Content fields
	 */
	protected function write(VersionId $versionId, Language $language, array $fields): void
	{
		$data = $this->getCacheData();
		$fields['_modified'] = time();
		$data[$versionId->value()][$language->code()] = $fields;
		$this->setCacheData($data);
	}

	/**
	 * Store submission reference in cache
	 */
	public function storeReference(): void
	{
		if (!($this->model instanceof SubmissionPage)) {
			return;
		}

		/** @var SubmissionPage $submission */
		$submission = $this->model;

		if (!$submission->exists()) {
			// Store metadata that can be used to reconstruct the submission
			$this->cache->set($submission->slug(), [
				'type' => 'submission',
				'template' => $submission->intendedTemplate()->name(),
				'slug' => $submission->slug(),
				'parent' => $submission->parent()?->id(),
				// Content is already stored via the storage handler's write() method
			], 60 * 24); // 24 hours
		}
		// If exists on disk, the reference will be passed via request body
	}

	/**
	 * Clean up cache data for this submission
	 */
	public function cleanup(): void
	{
		$this->cache->remove($this->cacheKey());

		if ($this->model instanceof SubmissionPage) {
			$this->cache->remove($this->model->slug());
		}
	}
}
