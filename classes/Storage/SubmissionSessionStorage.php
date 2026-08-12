<?php

namespace tobimori\DreamForm\Storage;

use Kirby\Cms\App;
use Kirby\Cms\Language;
use Kirby\Content\Storage;
use Kirby\Content\VersionId;
use tobimori\DreamForm\DreamForm;
use tobimori\DreamForm\Models\SubmissionPage;

/**
 * Storage handler that uses PHP sessions for storing submission data
 */
class SubmissionSessionStorage extends Storage
{
	/**
	 * Returns the session key for storing submission data
	 */
	protected function sessionKey(): string
	{
		return DreamForm::SESSION_KEY . ':data:' . $this->model->id();
	}

	/**
	 * Get submission data from session
	 */
	protected function getSessionData(): array
	{
		return App::instance()->session()->get($this->sessionKey(), []);
	}

	/**
	 * Set submission data in session
	 */
	protected function setSessionData(array $data): void
	{
		App::instance()->session()->set($this->sessionKey(), $data);
	}

	/**
	 * Deletes an existing version in an idempotent way
	 */
	public function delete(VersionId $versionId, Language $language): void
	{
		$data = $this->getSessionData();
		unset($data[$versionId->value()][$language->code()]);
		$this->setSessionData($data);
	}

	/**
	 * Checks if a version exists
	 */
	public function exists(VersionId $versionId, Language $language): bool
	{
		$data = $this->getSessionData();
		return isset($data[$versionId->value()][$language->code()]);
	}

	/**
	 * Returns the modification timestamp of a version if it exists
	 */
	public function modified(VersionId $versionId, Language $language): int|null
	{
		$data = $this->getSessionData();
		return $data[$versionId->value()][$language->code()]['_modified'] ?? null;
	}

	/**
	 * Returns the stored content fields
	 *
	 * @return array<string, string>
	 */
	public function read(VersionId $versionId, Language $language): array
	{
		$data = $this->getSessionData();
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

		$data = $this->getSessionData();
		$data[$versionId->value()][$language->code()]['_modified'] = time();
		$this->setSessionData($data);
	}

	/**
	 * Writes the content fields of an existing version
	 *
	 * @param array<string, string> $fields Content fields
	 */
	protected function write(VersionId $versionId, Language $language, array $fields): void
	{
		$data = $this->getSessionData();
		$fields['_modified'] = time();
		$data[$versionId->value()][$language->code()] = $fields;
		$this->setSessionData($data);
	}

	/**
	 * Store submission reference in session
	 */
	public function storeReference(): void
	{
		if (!($this->model instanceof SubmissionPage)) {
			return;
		}

		$submission = $this->model;
		$session = App::instance()->session();

		if ($submission->exists()) {
			// Page exists on disk - just store the slug
			$session->set(DreamForm::SESSION_KEY, $submission->slug());
		} else {
			// Page doesn't exist - store a data array that can be used to reconstruct it
			$session->set(DreamForm::SESSION_KEY, [
				'type' => 'submission',
				'template' => $submission->intendedTemplate()->name(),
				'slug' => $submission->slug(),
				'parent' => $submission->parent()?->id(),
				// Content is already stored via the storage handler's write() method
			]);
		}
	}

	/**
	 * Clean up session data for this submission
	 */
	public function cleanup(): void
	{
		App::instance()->session()->remove($this->sessionKey());
		App::instance()->session()->remove(DreamForm::SESSION_KEY);
	}
}
