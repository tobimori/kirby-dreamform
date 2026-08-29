---
title: Precognition
description: Real-time field validation with HTMX
---

Precognition validates fields as a user enters data. It is available only in HTMX mode.

## Prerequisites

- **HTMX mode:** Set the DreamForm mode to `htmx`.
- **HTMX 4:** No morph extension is necessary because morphing is part of HTMX 4.
- **HTMX 2:** Install the [Idiomorph extension](https://htmx.org/extensions/idiomorph/).

See the [HTMX support guide](../developers/htmx-support) for the version-specific installation instructions.

## Configuration

Enable Precognition in your config:

```php
// site/config/config.php

return [
  'tobimori.dreamform' => [
    'mode' => 'htmx',
    'htmx' => [
      'version' => 4,
    ],
    'precognition' => true,
  ],
];
```

Remove the `htmx` block, or set its version to `2`, when your site uses HTMX 2.

## How it works

- Fields validate automatically as the user enters or changes values.
- Text fields wait 500 milliseconds after input stops.
- Select, radio, and checkbox fields validate immediately.
- DreamForm validates only the field that caused the request.
- Precognitive validation does not complete the form submission.
- HTMX 2 uses Idiomorph to keep input focus. HTMX 4 uses its built-in `outerMorph` swap.

## Partial submissions

When Precognition is enabled, you can also enable partial submissions:

```php
// site/config/config.php

return [
  'tobimori.dreamform' => [
    // ...
    'partialSubmissions' => true,
  ],
];
```

This option saves each field value while the user completes the form. It helps keep data from abandoned or failed submissions.
