---
title: Advanced Configuration
---

## Custom Referer Page Resolution

If your pages use custom URLs (by overriding the `url()` method), DreamForm may not be able to find the referer page when processing submissions. The referer page is used in email actions and for redirecting users back to the form after submission.

You can provide a custom resolver function to handle these cases:

```php
// site/config/config.php

return [
  'tobimori.dreamform' => [
    'refererPageResolver' => function (string $referer): ?\Kirby\Cms\Page {
      // Example: Handle custom blog URLs like /blog/my-title/some-uid
      if (preg_match('#^/blog/([^/]+)/([^/]+)$#', $referer, $matches)) {
        $slug = $matches[1];
        $uid = $matches[2];
        
        // Find the page by UID
        return page('blog')->children()->find($uid);
      }
      
      // Example: Handle virtual pages or routes
      if (str_starts_with($referer, '/products/')) {
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
- `$referer`: The referer path from the submission

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
- The referer URL is preserved exactly as submitted, including any query parameters or fragments
