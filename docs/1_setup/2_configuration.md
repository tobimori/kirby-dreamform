---
title: Configuration
description: Configure DreamForm to your needs
---

## Configuration Options

### Encryption

| Option | Default | Accepts | Description |
| --- | --- | --- | --- |
| tobimori.dreamform.secret | `null` | `string|callable` | Optional encryption secret for published values; takes precedence over Kirby's `content.salt` |

HTMX and API modes require either `tobimori.dreamform.secret` or an explicitly configured Kirby [`content.salt`](https://getkirby.com/docs/reference/system/options/content#salt-for-drafts-and-media-files). DreamForm does not use Kirby's insecure default content salt.

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
  'content' => [
    'salt' => fn () => env('CONTENT_SALT')
  ],
  'tobimori.dreamform' => [
    'mode' => 'htmx',
    'debug' => false,
    'layouts' => ['1/1', '1/2, 1/2', '1/3, 1/3, 1/3'],
    'guards' => [
      'available' => ['csrf', 'honeypot', 'turnstile']
    ]
  ]
];
```
