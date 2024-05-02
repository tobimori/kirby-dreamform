<?php

namespace tobimori\DreamForm\Guards;

use Kirby\Cms\App;

class RatelimitGuard extends Guard
{
	public function run(): void
	{
		$kirby = App::instance();
		$ip = sha1($kirby->visitor()->ip()); // hash the IP address to protect user privacy

		$count = static::cache(
			$ip,
			fn () => $kirby->option('tobimori.dreamform.guards.ratelimit.limit'), // set the initial count
			$kirby->option('tobimori.dreamform.guards.ratelimit.interval') // set the expiration time
		);

		if ($count <= 0) {
			$this->cancel(t('dreamform.submission.error.ratelimit'), true);
		} else {
			static::setCache($ip, $count - 1, $kirby->option('tobimori.dreamform.guards.ratelimit.interval'));
		}
	}
}
