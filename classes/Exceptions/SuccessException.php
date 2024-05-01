<?php

namespace tobimori\DreamForm\Exceptions;

use Kirby\Exception\Exception;

/**
 * Finish the form submission early
 */
class SuccessException extends Exception
{
	public function shouldContinue(): bool
	{
		return false;
	}
}
