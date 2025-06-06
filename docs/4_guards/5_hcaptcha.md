---
title: hCaptcha
description: hCaptcha integration for bot protection
---

DreamForm has built-in support for using [hCaptcha](https://www.hcaptcha.com/), a privacy-focused captcha service that helps protect your forms from bots and automated abuse while preserving user privacy.

## Adding hCaptcha

Follow [the hCaptcha documentation](https://docs.hcaptcha.com/#get-your-hcaptcha-sitekey-and-secret-key) to register your site and get a site key & secret key. Proceed by adding the guard and the required keys to your `config.php`.

```php
// site/config/config.php

return [
  'tobimori.dreamform' => [
    'guards' => [
      'available' => ['hcaptcha', /* other guards here */ ],
      'hcaptcha' => [
        'siteKey' => fn () => env('HCAPTCHA_SITE_KEY'),
        'secretKey' => fn () => env('HCAPTCHA_SECRET_KEY')
      ]
    ],
  ],
];
```

Ideally, you should not commit these keys to your repository, but instead load them from environment variables, e.g. using the [kirby-dotenv plugin by Bruno Meilick](https://github.com/bnomei/kirby3-dotenv), as shown in the example above.

## Custom Themes

hCaptcha supports custom themes for Pro and Enterprise accounts. You can either use the built-in themes or create a fully custom theme that matches your brand.

### Built-in Themes

```php
'hcaptcha' => [
  'theme' => 'dark', // 'auto', 'light', or 'dark'
  // ... other options
]
```

### Custom Theme

For Pro and Enterprise accounts, you can define a custom theme:

```php
'hcaptcha' => [
  'theme' => 'custom',
  'customTheme' => [
    'palette' => [
      'mode' => 'light',
      'primary' => [
        'main' => '#00838F'
      ],
      'text' => [
        'heading' => '#555555',
        'body' => '#555555'
      ],
      // ... see full options below
    ]
  ]
]
```

The custom theme object supports extensive customization of colors and components. Check the [hCaptcha custom themes documentation](https://docs.hcaptcha.com/custom_themes/) for all available options.

## Options

| Option | Default | Accepts | Description |
| --- | --- | --- | --- |
| tobimori.dreamform.guards.hcaptcha.siteKey | `null` | `string|callback` | The hCaptcha sitekey is used to render hCaptcha on your site |
| tobimori.dreamform.guards.hcaptcha.secretKey | `null` | `string|callable` | The hCaptcha secret key allows communication between DreamForm and hCaptcha to verify responses |
| tobimori.dreamform.guards.hcaptcha.injectScript | `true` | `boolean` | Whether the client-side script should be injected implicitly by the plugin |
| tobimori.dreamform.guards.hcaptcha.theme | `'auto'` | `'auto'|'light'|'dark'|'custom'|array` | Theme to render the captcha with. Can be a string for built-in themes or an array for custom themes |
| tobimori.dreamform.guards.hcaptcha.size | `'normal'` | `'normal'|'compact'` | Set the size of the widget |
| tobimori.dreamform.guards.hcaptcha.customTheme | `null` | `array` | Custom theme configuration object (Pro/Enterprise only) |

## IP Address Collection

hCaptcha can optionally use the user's IP address for enhanced verification. To enable this, add `'ip'` to your metadata collection configuration:

```php
'tobimori.dreamform' => [
  'metadata' => [
    'collect' => ['ip'] // Enable IP collection
  ]
]
```

When IP collection is enabled, the user's IP address will be sent to hCaptcha for improved bot detection and risk scoring (Enterprise accounts).