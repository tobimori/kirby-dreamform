<?php

namespace tobimori\DreamForm\Models;

use Exception;
use Kirby\Cms\App;
use Kirby\Toolkit\A;
use tobimori\DreamForm\Exceptions\PerformerException;
use tobimori\DreamForm\Exceptions\SuccessException;
use tobimori\DreamForm\Fields\FileUploadField;
use tobimori\DreamForm\Jobs\SubmissionJob;
use tobimori\Queues\Queues;

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
	 * Handles the form submission precognitive guards
	 * @internal
	 */
	public function handlePrecognitiveGuards(): SubmissionPage
	{
		foreach ($this->form()->guards() as $guard) {
			$guard->precognitiveRun();
		}

		return $this;
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
		$allFieldsEmpty = true;
		$hasRequiredFields = false;

		foreach ($this->form()->formFields($currentStep) as $field) {
			// skip "decorative" fields that don't have a value
			if (!$field::hasValue()) {
				continue;
			}

			// create a field instance & set the value from the request
			$field = $this->updateFieldFromRequest($field);

			// check if this field is required
			if ($field->block()->required()->toBool()) {
				$hasRequiredFields = true;
			}

			// validate the field
			$validation = $field->validate();

			$this->setField($field);
			if ($validation !== true) {
				// if the validation fails, set an error in the submission state
				$this->setError(field: $field->key(), message: $validation);
			} else {
				$this->removeError($field->key());
			}

			// check if at least one field is not empty
			if (!$field->isEmpty()) {
				$allFieldsEmpty = false;
			}
		}

		// only show empty fields error on final step when no required fields exist
		if ($this->isFinalStep() && $allFieldsEmpty && !$hasRequiredFields) {
			$this->setError(t('dreamform.submission.error.emptyFields'));
		} else {
			$this->removeError();
		}

		return $this;
	}

	/**
	 * Returns whether the request goes to the previous form step
	 * @internal
	 */
	public function isPreviousStepRequest(): bool
	{
		return App::instance()->request()->body()->get('dreamform:action') === 'previous';
	}

	/**
	 * Stores current values and goes to the previous form step
	 * @internal
	 */
	public function handlePreviousStep(): SubmissionPage
	{
		if (!$this->form()->isMultiStep() || $this->currentStep() <= 1) {
			return $this;
		}

		foreach ($this->form()->formFields($this->currentStep()) as $field) {
			if (!$field::hasValue() || $field instanceof FileUploadField) {
				continue;
			}

			$this->setField($this->updateFieldFromRequest($field));
		}

		return $this->clearErrors()->previousStep();
	}

	/**
	 * Run the actions for the submission
	 * @internal
	 */
	public function handleActions(bool $force = false): SubmissionPage
	{
		if (
			$force ||
			$this->isFinalStep()
			&& $this->isSuccessful()
			&& $this->isHam()
		) {
			$this->updateState(['actionsdidrun' => true]);

			$actions = $this->createActions(force: $force);

			// check if queue support is enabled for this form
			// queues require submissions to be stored (so the job can retrieve them)
			$useQueue = $this->form()->content()->get('runWorkflowInQueue')->toBool()
				&& $this->form()->storeSubmissions()->toBool()
				&& class_exists('tobimori\Queues\Queues');

			if ($useQueue) {
				// separate actions into immediate and queueable
				// TODO: currently, all conditional nested actions are run immediately
				// figure out a way to handle conditional nested actions in bg
				$immediateActions = $actions->filter(fn ($action) => !$action->supportsQueues());
				$queueableActions = $actions->filter(fn ($action) => $action->supportsQueues());

				// run immediate actions synchronously
				foreach ($immediateActions as $action) {
					try {
						$action->run();
					} catch (Exception $e) {
						$this->handleActionException($e, $action);
					}
				}

				// queue the queueable actions if there are any
				if ($queueableActions->isNotEmpty()) {
					Queues::push('dreamform-submission', [
						'submissionUuid' => $this->uuid()->toString(),
						'actionBlocks' => $queueableActions->map(fn ($action) => [
							'id' => $action->block()->id(),
							'type' => $action->block()->type(),
							'content' => $action->block()->content()->toArray()
						])->data(),
						'force' => $force
					]);
				}
			} else {
				// run synchronously
				foreach ($actions as $action) {
					try {
						$action->run();
					} catch (Exception $e) {
						$this->handleActionException($e, $action);
					}
				}
			}
		}

		return $this;
	}

	/**
	 * Handle exceptions from action execution
	 * @internal
	 */
	protected function handleActionException(Exception $e, $action): void
	{
		// we only want to log "unknown" exceptions
		if (
			$e instanceof PerformerException || $e instanceof SuccessException
		) {
			if (!$e->shouldContinue()) {
				throw $e;
			}

			return;
		}

		$this->addLogEntry([
			'text' => $e->getMessage(),
			'template' => [
				'type' => $action->type(),
			]
		], type: 'error', icon: 'alert', title: "dreamform.submission.log.error");
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
			foreach ($this->form()->formFields($currentStep) as $field) {
				$field->afterSubmit($this);
			}
		}

		return $this;
	}
}
