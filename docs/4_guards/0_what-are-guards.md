---
title: What are Guards?
description: Learn about guards that protect your forms from spam and abuse
---

Guards are a way to prevent form submission [before field validation](https://plugins.andkindness.com/dreamform/docs/developers/under-the-hood). They are intended for security checks, like CSRF, honeypot or captchas.

Conceptually, guards and actions are similar. **Unlike** guards, actions are ran after form validation & can be configured with form-specific settings through the panel. Guards can be enabled or disabled **globally only** in the config.

By default, CSRF & honeypot guards are enabled. It is most likely the responsibility of the sites' developer and not meant to be changed by the editor.

Some guards might have a "silent" option, which means that they will not show any error messages to the user, but instead show the default success screen, **except** if debug mode is enabled. Keep this in mind when reporting potential issues.

## Available guards

### Built-in Guards

* [Akismet](1_akismet.md) - Spam filtering with Akismet API
* [CSRF](2_csrf.md) - Cross-Site Request Forgery protection
* [Honeypot](3_honeypot.md) - Hidden field spam protection
* [Turnstile](4_turnstile.md) - Cloudflare Turnstile captcha integration
* [Rate Limit](5_ratelimit.md) - IP-based rate limiting for form submissions

### Creating Custom Guards

* [Custom Guards](6_custom.md) - Learn how to create your own custom guards

### Guards created by the Community

* None

## Configuring guards

You can enable or disable used guards in your `config.php` by supplying an array to `tobimori.dreamform.guards.available` with the types. When activating new guards, make sure to check the documentation of each Guard to see if they require additional configuration.

```php
// site/config/config.php

return [
  'tobimori.dreamform' => [
    'guards' => [
      'available' => ['csrf', 'honeypot', 'turnstile', 'ratelimit', /* other guards here */ ],
    ],
  ],
];
```

### Options

| Option | Default | Accepts | Description |
| --- | --- | --- | --- |
| tobimori.dreamform.guards.available | `['csrf', 'honeypot']` | `array` | Active guards to protect your form |