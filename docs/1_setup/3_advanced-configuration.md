---
title: Advanced Configuration
description: Configure caches, custom referer resolution and headless setups
---

## Custom Referer Page Resolution

If your pages use custom URLs (by overriding the `url()` method), DreamForm may not be able to find the referer page when processing submissions. The referer page is available as `$page` in email templates and queries of the [Email action](../3_actions/4_email.md). Redirects back to the form use the stored referer path directly and do not use the resolver.

You can provide a custom resolver function to handle these cases:

```php
// site/config/config.php

return [
  'tobimori.dreamform' => [
    'refererPageResolver' => function (?string $referer, \tobimori\DreamForm\Models\SubmissionPage $submission): ?\Kirby\Cms\Page {
      if ($referer === null) {
        return null;
      }

      // Example: Handle custom blog URLs like blog/my-title/some-uid
      if (preg_match('#^blog/([^/]+)/([^/]+)$#', $referer, $matches)) {
        $slug = $matches[1];
        $uid = $matches[2];
        
        // Find the page by UID
        return page('blog')->children()->find($uid);
      }
      
      // Example: Handle virtual pages or routes
      if (str_starts_with($referer, 'products/')) {
        $productId = basename($referer);
        // Return your virtual product page
        return page('products')->find($productId);
      }
      
      // Fall back to default resolution
      return \tobimori\DreamForm\DreamForm::findPageOrDraftRecursive($referer);
    }
  ]
];
```

The resolver callback receives:
- `$referer`: The referer path from the submission, or `null`
- `$submission`: The submission page

The callback should return a `\Kirby\Cms\Page` object if a page is found, or `null` otherwise.

### Use Cases

This is particularly useful when:
- You have pages with custom URL schemes that don't match their content structure
- You're using virtual pages that don't exist in the content folder
- You're rendering pages through custom routes
- You want to map certain URLs to specific pages

### Important Notes

- The referer is captured from the browser's `Referer` header when the form is submitted
- Forms can be embedded on any page, so the referer represents where the form was displayed
- If no custom resolver is provided, DreamForm uses its default page lookup mechanism
- DreamForm only stores the path of the referer, without a leading slash, query parameters or fragments, e.g. `blog/my-title/some-uid`. On multi-language sites, the path includes the language prefix
- If the referer is from a different host, DreamForm stores no referer and the callback receives `null`

## Headless CMS Configuration

When you use Kirby as a headless CMS with `url` set to `/` in your config, the site has no host. DreamForm then tries to use the host of the current request to check whether the `Referer` header comes from your site.

```php
// site/config/config.php
return [
  'url' => '/', // Headless setup
];
```

With `url` set to `/`, Kirby also builds the request environment from this URL, so the request host is empty too. DreamForm then stores no referer. In `prg` mode, users are redirected to the site URL after a submission, and `$page` in email templates is `null`. If you need the referer, set `url` to the full URL of your site.

## Caches

DreamForm registers three Kirby plugin caches. All of them are active by default. By default, Kirby stores them as files in the `site/cache` folder.

| Option | Default | Accepts | Description |
| --- | --- | --- | --- |
| tobimori.dreamform.cache.sessionless | `['active' => true]` | `array` | Stores unsaved submissions in `api` mode and for HTMX requests |
| tobimori.dreamform.cache.fields | `['active' => true]` | `array` | Stores data that fields load from external sources |
| tobimori.dreamform.cache.performer | `['active' => true]` | `array` | Stores data of actions and guards, and the license check |

### Sessionless Cache

In `api` mode and for HTMX requests, DreamForm does not use the Kirby session. It stores the submission content and a reference to the submission in the `sessionless` cache for 24 hours. The form sends an encrypted reference with the next request, so DreamForm can continue the submission, for example for the next step of a [multi-step form](../0_general/2_multi-step.md) or for [Precognition](../0_general/4_precognition.md).

When DreamForm saves the submission to disk, it copies the data from the cache. DreamForm does not remove the cache data earlier when the submission is finished. See [Data Retention](../0_general/3_data-retention.md) for more information.

**Do not disable this cache if you use the `api` or `htmx` mode.** Without it, DreamForm cannot store submissions during these requests, and submissions fail. In `prg` mode, and for HTMX forms that fall back to a normal submission without JavaScript, DreamForm uses the Kirby session instead.

If your site runs on more than one server, all servers must read the same cache. Use a shared cache driver, such as Redis or Memcached, for this cache.

### Fields Cache

The [email field](../2_fields/5_email.md) stores the list of disposable email domains in the `fields` cache for 10 minutes. If you disable this cache, DreamForm downloads the list again for every validation of an email field.

### Performer Cache

Actions and guards store data in the `performer` cache. Unless noted otherwise, entries expire after 10 minutes:

- Newsletter actions, such as [Mailchimp](../3_actions/10_mailchimp.md), [MailerLite](../3_actions/9_mailerlite.md), [Buttondown](../3_actions/7_buttondown.md), [Brevo](../3_actions/11_brevo.md) and [Loops](../3_actions/12_loops.md), store the data they load from the API, such as the result of the API key check, lists, groups, tags or fields
- The [Akismet guard](../4_guards/1_akismet.md) stores the result of the API key check
- The [Rate Limit guard](../4_guards/6_ratelimit.md) stores the number of remaining submissions for each visitor. These entries expire after the configured interval
- DreamForm stores a successful license check for 24 hours

If you disable this cache, DreamForm sends the API requests again each time it needs the data. **The Rate Limit guard does not block any submissions without this cache**, since it cannot remember the previous submissions.

### Configure the Caches

The caches accept the same options as any other Kirby cache, such as `active`, `type` and the options of the cache driver. See the [Kirby cache documentation](https://getkirby.com/docs/reference/system/options/cache) for all options.

```php
// site/config/config.php

return [
  'tobimori.dreamform' => [
    'cache' => [
      // use Redis for sessionless submissions
      'sessionless' => [
        'type' => 'redis',
        'host' => '127.0.0.1',
        'port' => 6379
      ],
      // disable the fields cache
      'fields' => [
        'active' => false
      ]
    ]
  ]
];
```

To remove old data, for example after you add a list in Mailchimp, delete the cache folder or flush the cache with your cache driver.
