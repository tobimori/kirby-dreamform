---
title: Turnstile
description: Cloudflare Turnstile captcha integration
---

DreamForm has built-in support for using [Turnstile](https://www.cloudflare.com/de-de/products/turnstile/), a free captcha alternative provided by Cloudflare. It can be embedded into any website, even without sending traffic through Cloudflare.

## Adding Turnstile

Follow [the CloudFlare documentation](https://developers.cloudflare.com/turnstile/get-started/#get-a-sitekey-and-secret-key) on getting a site key & secret key. Proceed by adding the guard and the required keys to your `config.php`.

```php
// site/config/config.php

return [
  'tobimori.dreamform' => [
    'guards' => [
      'available' => ['turnstile', /* other guards here */ ],
      'turnstile' => [
        'siteKey' => fn () => env('TURNSTILE_SITE_KEY'),
        'secretKey' =>  fn () => env('TURNSTILE_SECRET_KEY')
      ]
    ],
  ],
];
```

Ideally, you should not commit these keys to your repository, but instead load it from an environment variable, e.g. using the [kirby-dotenv plugin by Bruno Meilick](https://github.com/bnomei/kirby3-dotenv), as shown in the example above.

## Options

| Option | Default | Accepts | Description |
| --- | --- | --- | --- |
| tobimori.dreamform.guards.turnstile.siteKey | `null` | `string|callback` | The Turnstile sitekey is used to invoke Turnstile on your site |
| tobimori.dreamform.guards.turnstile.secretKey | `null` | `string|callable` | The Turnstile secret key allows communication between DreamForm and Cloudflare to response |
| tobimori.dreamform.guards.turnstile.injectScript | `true` | `boolean` | Whether the client-side script should be injected implicitly by the plugin |
| tobimori.dreamform.guards.turnstile.theme | `'auto'` | `'auto'|'light'|'dark'` | Theme to render the captcha with |