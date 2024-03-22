<?php

namespace tobimori\DreamForm\Guards;

class RatelimitGuard extends Guard
{
	public function run(): void
	{
		// TODO: store hashed IP address and timestamp for x minutes (configurable)
	}
}
