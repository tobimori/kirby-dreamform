---
title: HTMX Support
description: Progressive enhancement with HTMX
---

[HTMX](https://htmx.org/) is a small JavaScript library that lets HTML elements make requests and update parts of a page. DreamForm requires HTMX 4. Forms still work with a normal browser submission when JavaScript is not available.

## Use HTMX

1. Install [HTMX 4](https://four.htmx.org/docs/#installing-htmx) in your site.
2. Set the DreamForm mode to `htmx`.

```php
// site/config/config.php
<?php

return [
  'tobimori.dreamform' => [
    'mode' => 'htmx',
  ],
];
```

Precognition uses the DOM morphing that is part of HTMX, so it does not need an extension.

## HTMX behavior

HTMX has a default request timeout of 60 seconds. Configure `htmx.config.defaultTimeout` in your site if a form action can take more than 60 seconds.

HTMX swaps `4xx` and `5xx` responses by default. DreamForm returns validation errors as normal HTML responses, so they do not need an additional HTMX setting.

## Limitations

HTMX requires the default snippets or a minimally changed snippet under the same `dreamform/form` name. An HTMX request renders only the form and not the complete page.

Custom arguments in the form snippet are not available in later HTMX requests. With Staticache, forms cannot use the normal non-JavaScript submission from a cached page.
