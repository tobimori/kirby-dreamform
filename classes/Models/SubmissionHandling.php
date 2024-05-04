<?php

namespace tobimori\DreamForm\Models;

use Exception;
use Kirby\Cms\App;
use Kirby\Toolkit\A;
use tobimori\DreamForm\Exceptions\PerformerException;
use tobimori\DreamForm\Exceptions\SuccessException;

/**
 * Handle the submission process
 */
trait SubmissionHandling
{
	abstract public function form(): FormPage;

	/**
	 * Apply a Kirby hook to the submission
	 * @internal
	 */
	public function applyHook(string $type = 'before'): SubmissionPage
	{
		if (!A::has(['before', 'after'], $type)) {
			throw new \Exception('[DreamForm] Unknown hook type');
		}

		return App::instance()->apply(
			"dreamform.submit:{$type}",
			['submission' => $this, 'form' => $this->form()],
			'submission'
		);
	}

	/**
	 * Handles the form submission guards
	 * @internal
	 */
	public function handleGuards(bool $postValidation = false): SubmissionPage
	{
		foreach ($this->form()->guards() as $guard) {
			$postValidation ? $guard->postValidation($this) : $guard->run();
		}

		return $this;
	}

	/**
	 * Validates the fields and collects values from the request
	 * @internal
	 */
	public function handleFields()
	{
		$currentStep = App::instance()->request()->query()->get('dreamform-step', 1);
		foreach ($this->form()->fields($currentStep) as $field) {
			// skip "decorative" fields that don't have a value
			if (!$field::hasValue()) {
				continue;
			}

			// create a field instance & set the value from the request
			$field = $this->updateFieldFromRequest($field);

			// validate the field
			$validation = $field->validate();

			if ($validation !== true) {
				// if the validation fails, set an error in the submission state
				$this->setError(field: $field->key(), message: $validation);
			} else {
				// otherwise add it to the content of the submission
				$this->setField($field)->removeError($field->key());
			}
		}

		return $this;
	}

	/**
	 * Run the actions for the submission
	 * @internal
	 */
	public function handleActions(bool $force = false): SubmissionPage
	{
		if (
			$force ||
			($this->isFinalStep()
				&& $this->isSuccessful()
				&& $this->isHam())
		) {
			$this->updateState(['actionsdidrun' => true]);
			foreach ($this->createActions(force: $force) as $action) {
				try {
					$action->run();
				} catch (Exception $e) {
					// we only want to log "unknown" exceptions
					if (
						$e instanceof PerformerException || $e instanceof SuccessException
					) {
						if (!$e->shouldContinue()) {
							throw $e;
						}

						continue;
					}

					$this->addLogEntry([
						'text' => $e->getMessage(),
						'template' => [
							'type' => $action->type(),
						]
					], type: 'error', icon: 'alert', title: "dreamform.submission.log.error");
				}
			}
		}

		return $this;
	}

	/**
	 * Finishes the form submission or advances to the next step
	 * @internal
	 */
	public function finalize(): SubmissionPage
	{
		if (!$this->isSuccessful()) {
			return $this;
		}

		if ($this->isFinalStep()) {
			return $this->finish();
		}

		return $this->advanceStep();
	}

	/**
	 * Handles the after-submit hooks for the fields
	 * @internal
	 */
	public function handleAfterSubmitFields(): SubmissionPage
	{
		$currentStep = App::instance()->request()->query()->get('dreamform-step', 1);
		if ($this->isSuccessful()) {
			foreach ($this->form()->fields($currentStep) as $field) {
				$field->afterSubmit($this);
			}
		}

		return $this;
	}
}
