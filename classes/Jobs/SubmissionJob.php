<?php

namespace tobimori\DreamForm\Jobs;

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
			$this->log('debug', 'hello!');
		}
	}
}
