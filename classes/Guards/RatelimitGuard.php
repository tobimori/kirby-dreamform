<?php

namespace tobimori\DreamForm\Guards;

use Kirby\Cms\App;
use tobimori\DreamForm\DreamForm;

class RatelimitGuard extends Guard
{
	public function run(): void
	{
		$ip = sha1(App::instance()->visitor()->ip()); // hash the IP address to protect user privacy

		$count = static::cache(
			$ip,
			fn () => DreamForm::option('guards.ratelimit.limit'), // set the initial count
			DreamForm::option('guards.ratelimit.interval') // set the expiration time
		);

		if ($count <= 0) {
			$this->cancel('dreamform.submission.error.ratelimit', public: true);
		} else {
			static::setCache($ip, $count - 1, DreamForm::option('guards.ratelimit.interval'));
		}
	}
}
