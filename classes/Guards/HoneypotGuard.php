<?php

namespace tobimori\DreamForm\Guards;

use tobimori\DreamForm\DreamForm;
use tobimori\DreamForm\Models\SubmissionPage;

class HoneypotGuard extends Guard
{
	public const TYPE = 'honeypot';

	public function fieldName(): string
	{
		$available = DreamForm::option('guards.honeypot.fields', []);
		$used = $this->form()->formFields()->map(fn ($field) => $field->key());

		foreach ($available as $field) {
			if (!in_array($field, $used->data())) {
				return $field;
			}
		}

		return 'dreamform-guard';
	}

	public function precognitiveRun(): void
	{
		$value = SubmissionPage::valueFromBody($this->fieldName());

		if ($value) {
			$this->silentCancel(t('dreamform.submission.error.honeypot'));
		}
	}

	public static function hasSnippet(): bool
	{
		return true;
	}
}
