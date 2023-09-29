<?php

namespace tobimori\DreamForm\Actions;

use tobimori\DreamForm\Models\FormPage;

abstract class Action
{
	public FormPage $page;

	public function ___construct(FormPage $page)
	{
		$this->page = $page;
	}

	abstract public function run(): void;

	/** Returns the Blocks fieldset blueprint for the actions' settings */
	abstract public static function blueprint(): array;

	protected function fail($message = null)
	{
		// TODO: Implement fail() method.
	}
}
