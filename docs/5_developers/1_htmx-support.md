---
title: HTMX Support
description: Progressive enhancement with HTMX 2 or HTMX 4
---

[HTMX](https://htmx.org/) is a small JavaScript library that lets HTML elements make requests and update parts of a page. DreamForm supports HTMX 2 and HTMX 4. Forms still work with a normal browser submission when JavaScript is not available.

HTMX 2 is the default in DreamForm 2. You must explicitly enable HTMX 4.

## Use HTMX 4

1. Install [HTMX 4](https://four.htmx.org/docs/#installing-htmx) in your site.
2. Set the DreamForm mode to `htmx`.
3. Set `htmx.version` to `4`.

```php
// site/config/config.php
<?php

return [
  'tobimori.dreamform' => [
    'mode' => 'htmx',
    'htmx' => [
      'version' => 4,
    ],
  ],
];
```

HTMX 4 includes DOM morphing. Precognition does not need the Idiomorph extension when you use HTMX 4.

<details markdown="1">
<summary><strong>Use HTMX 2</strong></summary>

HTMX 2 stays active by default when you do not set `htmx.version`.

1. Follow the [HTMX 2 installation guide](https://htmx.org/docs/#installing) to include the library in your project.
2. If you use Precognition, also install the [Idiomorph extension](https://htmx.org/extensions/idiomorph/).
3. Set the DreamForm mode to `htmx`.

```php
// site/config/config.php
<?php

return [
  'tobimori.dreamform' => [
    'mode' => 'htmx',
    // HTMX 2 is the default.
  ],
];
```

You can also explicitly set the version:

```php
'htmx' => [
  'version' => 2,
],
```

</details>

## Version-specific output

DreamForm generates only the attributes for the selected HTMX version. Do not load HTMX 2 and HTMX 4 on the same page.

| Feature | HTMX 2 | HTMX 4 |
| --- | --- | --- |
| Disable submit buttons | `hx-disabled-elt` | `hx-disable:inherited` |
| Form request values | `hx-vals` | `hx-vals:inherited` |
| Precognition swap | Idiomorph `morph` | Built-in `outerMorph` |
| Attribute inheritance | Implicit | Explicit with `:inherited` |
| Request source | `HX-Trigger` | `HX-Source` |

The [`htmx-2-compat` extension](https://four.htmx.org/extensions/htmx-2-compat/) can help sites that have other old HTMX markup. DreamForm does not require this extension.

## HTMX 4 behavior changes

HTMX 4 has a default request timeout of 60 seconds. HTMX 2 has no default timeout. Configure `htmx.config.defaultTimeout` in your site if a form action can take more than 60 seconds.

HTMX 4 also swaps `4xx` and `5xx` responses by default. DreamForm validation responses use normal HTML responses and do not require a global compatibility setting.

## Limitations

HTMX requires the default snippets or a minimally changed snippet under the same `dreamform/form` name. An HTMX request renders only the form and not the complete page.

Custom arguments in the form snippet are not available in later HTMX requests. With Staticache, forms cannot use the normal non-JavaScript submission from a cached page.
