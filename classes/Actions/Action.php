<?php

namespace tobimori\DreamForm\Actions;

use Kirby\Cms\Block;
use tobimori\DreamForm\Models\FormPage;
use tobimori\DreamForm\Models\SubmissionPage;

abstract class Action
{
	private Block $action;
	private FormPage $form;
	private SubmissionPage $submission;

	public function __construct(Block $action, FormPage $form, SubmissionPage $submission)
	{
		$this->action = $action;
		$this->form = $form;
		$this->submission = $submission;
	}

	/** Returns the Form this action is being run on */
	public function form(): FormPage
	{
		return $this->form;
	}


	/** Returns the Submission this action is being run on */
	public function submission(): SubmissionPage
	{
		return $this->submission;
	}

	/** Returns the action configuration */
	public function action(): Block
	{
		return $this->action;
	}

	/** Run the action */
	abstract public function run(): void;

	/** Returns the Blocks fieldset blueprint for the actions' settings */
	abstract public static function blueprint(): array;
}
