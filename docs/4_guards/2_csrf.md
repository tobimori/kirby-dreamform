---
title: CSRF
description: Cross-Site Request Forgery protection
---

The **CSRF guard** is enabled by default. It checks if the form submission contains a valid CSRF token. If not, the submission will be rejected.

CSRF tokens are unique to the user session. This means, they can't be used together with the Kirby cache. If you want to use the cache, you have to disable the CSRF guard in the config. Make sure it's okay in your case to disable generating CSRF tokens, like when a form is not used to transfer sensitive data.

You can read more [about CSRF attacks on the OWASP website](https://owasp.org/www-community/attacks/csrf).

## Disabling the CSRF guard

You can set the executed guards globally in your `config.php`.

```php
// site/config/config.php

return [
  // Settings for the DreamForm plugin
  'tobimori.dreamform' => [
    'guards' => [
      // disable csrf since we want to be sessionless
      // so that the page can be cached
      'available' => ['honeypot']
    ],
  ],
];
```