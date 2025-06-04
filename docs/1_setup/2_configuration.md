---
title: Configuration
description: Configure DreamForm to your needs
---

## Configuration Options

### Required Settings

| Option | Default | Accepts | Description |
| --- | --- | --- | --- |
| tobimori.dreamform.secret | `null` | `string|callable` | Encryption secret for published values (required for HTMX and API modes) |

### Optional Settings

| Option | Default | Accepts | Description |
| --- | --- | --- | --- |
| tobimori.dreamform.mode | `'prg'` | `'prg'|'api'|'htmx'` | Set the submission mode for all form submissions |
| tobimori.dreamform.multiStep | `true` | `boolean` | Enable or disable multi-step forms |
| tobimori.dreamform.storeSubmissions | `true` | `boolean` | Whether to store submissions as pages in Kirby |
| tobimori.dreamform.debug | `fn () => option('debug')` | `boolean|callable` | If enabled, sensitive errors are shown on form submission |
| tobimori.dreamform.layouts | `['1/1', '1/2, 1/2']` | `array` | Enabled layouts for the form builder |
| tobimori.dreamform.page | `page://forms` | `string` | The page where all forms are stored |
| tobimori.dreamform.integrations.gravatar | `true` | `boolean` | If enabled, submissions with email fields fetch an avatar from Gravatar to show in the panel |
| tobimori.dreamform.refererPageResolver | `callable` | `callable` | Custom callback to resolve pages with custom URLs |

## Example Configuration

```php
// site/config/config.php

return [
  'tobimori.dreamform' => [
    'secret' => fn () => env('DREAMFORM_SECRET'),
    'mode' => 'htmx',
    'debug' => false,
    'layouts' => ['1/1', '1/2, 1/2', '1/3, 1/3, 1/3'],
    'guards' => [
      'available' => ['csrf', 'honeypot', 'turnstile']
    ]
  ]
];
```
