---
title: Configuration
description: Configure DreamForm to your needs
---

Options let you fine-tune the behavior of DreamForm. Set them in your `config.php` under the `tobimori.dreamform` key. This page lists only the general options. Options for [fields](../2_fields/0_what-are-fields.md), [actions](../3_actions/0_what-are-actions.md) and [guards](../4_guards/0_what-are-guards.md) are on their own pages. For custom referer page resolution and headless setups, see [Advanced Configuration](3_advanced-configuration.md).

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
| tobimori.dreamform.htmx.version | `2` | `2|4` | Select the HTMX major version; HTMX 4 is opt-in |
| tobimori.dreamform.precognition | `false` | `boolean` | Validate fields while the user fills in the form; HTMX mode only, see [Precognition](../0_general/4_precognition.md) |
| tobimori.dreamform.partialSubmissions | `true` | `boolean` | Show the partial submissions toggle in the form settings; requires `htmx` mode, `precognition` and stored submissions |
| tobimori.dreamform.multiStep | `true` | `boolean` | Enable or disable multi-step forms |
| tobimori.dreamform.storeSubmissions | `true` | `boolean` | Whether to store submissions as pages in Kirby |
| tobimori.dreamform.metadata.collect | `[]` | `array` | Metadata to store with each submission: `'ip'` and/or `'userAgent'` |
| tobimori.dreamform.useDataAttributes | `false` | `boolean` | Output the form URL as `data-form-url` instead of the `action` attribute |
| tobimori.dreamform.hideEmptyErrors | `false` | `boolean` | Do not render error elements when there is no error message |
| tobimori.dreamform.debug | `fn () => option('debug')` | `boolean|callable` | If enabled, sensitive errors are shown on form submission |
| tobimori.dreamform.layouts | `['1/1', '1/2, 1/2']` | `array` | Enabled layouts for the form builder |
| tobimori.dreamform.page | `'page://forms'` | `string` | The page where all forms are stored |
| tobimori.dreamform.marks | `['bold', 'italic', 'underline', 'strike', 'link', 'email']` | `array` | Enabled marks for writer fields, such as success messages, email templates and option labels |
| tobimori.dreamform.nodes | `['heading', 'bulletList', 'orderedList']` | `array` | Enabled nodes for the success message and email template writer fields |
| tobimori.dreamform.integrations.gravatar | `true` | `boolean` | If enabled, submissions with email fields fetch an avatar from Gravatar to show in the panel |
| tobimori.dreamform.refererPageResolver | `fn (?string $referer) => DreamForm::findPageOrDraftRecursive($referer)` | `callable` | Custom callback to resolve pages with custom URLs, see [Advanced Configuration](3_advanced-configuration.md#custom-referer-page-resolution) |

## Example Configuration

```php
// site/config/config.php

return [
  'content' => [
    'salt' => fn () => env('CONTENT_SALT')
  ],
  'tobimori.dreamform' => [
    'mode' => 'htmx',
    'htmx' => [
      'version' => 4,
    ],
    'debug' => false,
    'layouts' => ['1/1', '1/2, 1/2', '1/3, 1/3, 1/3'],
    'guards' => [
      'available' => ['csrf', 'honeypot', 'turnstile']
    ]
  ]
];
```
