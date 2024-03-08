<?php

namespace tobimori\DreamForm\Actions;

use Kirby\Cms\Block;
use Kirby\Cms\Blocks;
use Kirby\Cms\Collection;
use Kirby\Toolkit\Str;
use tobimori\DreamForm\Exceptions\ActionException;
use tobimori\DreamForm\Models\FormPage;
use tobimori\DreamForm\Models\SubmissionPage;

/**
 * Base class for all actions.
 * @package tobimori\DreamForm\Actions
 */
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

	/** Abort the form submission */
	public function abort(string $message, bool $force = false): void
	{
		if (!$force) {
			$option = option('tobimori.dreamform.silentErrors');
			if (is_callable($option)) {
				$option = $option();
			}

			if ($option) {
				// TODO: log error?
				return;
			}
		}

		throw new ActionException($message);
	}

	/** Run the action */
	abstract public function run();

	/** Returns the Blocks fieldset blueprint for the actions' settings */
	abstract public static function blueprint(): array;

	public static function createFromBlocks(Blocks $blocks, FormPage $formPage, SubmissionPage $submissionPage): Collection
	{
		$active = option('tobimori.dreamform.actions', true);
		$registered = FormPage::$registeredActions;
		$actions = [];

		foreach ($blocks as $block) {
			$type = Str::replace($block->type(), '-action', '');

			if (!key_exists($type, $registered)) {
				continue;
			}

			if (is_array($active) && !in_array($type, $active) || $active != true) {
				continue;
			}

			$actions[] = new $registered[$type]($block, $formPage, $submissionPage);
		}

		return new Collection($actions, []);
	}
}
