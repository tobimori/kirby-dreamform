---
title: Rate Limit
description: IP-based rate limiting for form submissions
---

DreamForm supports adding a IP-based rate limit to form submissions. You can control the allowed number of requests in any interval.

Note that guards execute before field validation. This implies that even if you're navigating from one multi-step form page to the next, or if a request is rejected due to invalid input data, the rate limit will still be decremented.

Keep in mind that any subsequent requests that decrement the rate limit will reset the interval.

## Adding Rate limit

To get started with the Rate limit guard, add it to your `config.php` file as following. You can also customize the `limit` and `interval` settings as needed. By default, the guard will allow 10 requests within a 3-minute interval.

```php
// site/config/config.php

return [
  'tobimori.dreamform' => [
    'guards' => [
      'available' => ['ratelimit', /* other guards here */ ],
      'ratelimit' => [
        'limit' => 10,
        'interval' => 3
      ]
    ],
  ],
];
```

## Options

| Option | Default | Accepts | Description |
| --- | --- | --- | --- |
| tobimori.dreamform.guards.ratelimit.limit | `10` | `int` | Limit of requests per interval |
| tobimori.dreamform.guards.ratelimit.interval | `3` | `int` | Interval in minutes |