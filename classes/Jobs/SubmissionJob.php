<?php

namespace tobimori\DreamForm\Jobs;

use Exception;
use Kirby\Cms\Block;
use Kirby\Toolkit\Str;
use tobimori\DreamForm\DreamForm;
use tobimori\DreamForm\Exceptions\PerformerException;
use tobimori\DreamForm\Exceptions\SuccessException;
use tobimori\DreamForm\Models\SubmissionPage;
use tobimori\Queues\Job;

if (class_exists('tobimori\Queues\Job')) {
	class SubmissionJob extends Job
	{
		public function name(): string
		{
			return t('dreamform.queues.name');
		}

		public function type(): string
		{
			return 'dreamform-submission';
		}

		public function handle(): void
		{
			$submissionUuid = $this->payload['submissionUuid'] ?? null;
			$actionBlocks = $this->payload['actionBlocks'] ?? [];
			$force = $this->payload['force'] ?? false;

			$this->log('debug', "Processing submission: \"{$submissionUuid}\"");

			/** @var SubmissionPage $submission */
			$submission = DreamForm::findPageOrDraftRecursive($submissionUuid);
			if (!$submission || $submission->intendedTemplate()->name() !== 'submission') {
				$this->log('error', "Submission not found: \"{$submissionUuid}\"");
				return;
			}

			// recreate and execute each action
			foreach ($actionBlocks as $blockData) {
				try {
					$block = $this->createBlockFromData($blockData, $submission);

					$type = Str::replace($block->type(), '-action', '');
					$action = DreamForm::action($type, $block, $submission, $force);

					if ($action) {
						$this->log('debug', "Running action: \"{$action->type()}\"");
						$action->run();
						$this->log('debug', "Action completed: \"{$action->type()}\"");
					}
				} catch (Exception $e) {
					$this->handleActionException($e, $blockData['type'], $submission);
				}
			}
		}

		/**
		 * Create a Block instance from serialized data
		 */
		protected function createBlockFromData(array $data, SubmissionPage $submission): Block
		{
			return new Block([
				'id' => $data['id'] ?? null,
				'type' => $data['type'],
				'content' => $data['content'],
				'parent' => $submission->form()
			]);
		}

		/**
		 * Handle exceptions from action execution
		 */
		protected function handleActionException(Exception $e, string $actionType, SubmissionPage $submission): void
		{
			// log exceptions but don't throw them to prevent job failure
			if (
				$e instanceof PerformerException || $e instanceof SuccessException
			) {
				if (!$e->shouldContinue()) {
					$this->log('error', "Action failed (non-continuable): {$actionType} - \"{$e->getMessage()}\"");
					// in queue context, we can't throw, so we just log
				}
				return;
			}

			// log unknown exceptions
			$this->log('error', "Action failed: \"{$actionType}\" - \"{$e->getMessage()}\"");

			// try to add to submission log if possible
			try {
				$submission->addLogEntry([
					'text' => $e->getMessage(),
					'template' => [
						'type' => $actionType,
					]
				], type: 'error', icon: 'alert', title: "dreamform.submission.log.error");
			} catch (Exception $logException) {
				// if we can't log to submission, just log to job
				$this->log('error', "Failed to add log entry: \"{$logException->getMessage()}\"");
			}
		}
	}
}
