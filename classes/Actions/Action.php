<?php

namespace tobimori\DreamForm\Actions;

use Kirby\Cms\Api;
use Kirby\Cms\App;
use Kirby\Cms\Block;
use Kirby\Cms\Blocks;
use Kirby\Cms\Collection;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;
use tobimori\DreamForm\Exceptions\ActionException;
use tobimori\DreamForm\Exceptions\SuccessException;
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
	public function error(string $message, bool $force = false): void
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

	/** Finish the form submission early */
	public function success(): void
	{
		throw new SuccessException();
	}

	/** Run the action */
	abstract public function run();

	/** Returns the Blocks fieldset blueprint for the actions' settings */
	abstract public static function blueprint(): array;

	public static function isAvailable(): bool
	{
		return true;
	}

	public static function createFromBlocks(Blocks $blocks, FormPage $formPage, SubmissionPage $submissionPage): Collection
	{
		$active = option('tobimori.dreamform.actions', true);
		$registered = FormPage::$registeredActions;
		$actions = [];

		foreach ($blocks as $block) {
			$type = Str::replace($block->type(), '-action', '');

			// check if the action wanted is registered
			if (!key_exists($type, $registered)) {
				continue;
			}

			// check if the action wanted is set as active in config
			if (is_array($active) && !in_array($type, $active) || $active != true) {
				continue;
			}

			// check if the action is available
			// (e.g. MailchimpAction requires the Mailchimp API to be set up)
			if ($registered[$type]::isAvailable() === false) {
				continue;
			}

			$actions[] = new $registered[$type]($block, $formPage, $submissionPage);
		}

		return new Collection($actions, []);
	}

	public static function cache(string $key, callable $callback): mixed
	{
		$cache = App::instance()->cache('tobimori.dreamform.actions');
		if (!$cache) {
			return $callback();
		}

		$value = $cache->get($key);
		if ($value === null) {
			$value = $callback();
			$cache->set($key, $value, 10);
		}

		return $value;
	}

	public static function group(): string
	{
		return 'basic';
	}
}
