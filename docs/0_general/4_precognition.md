---
title: Precognition
description: Real-time field validation with HTMX
---

Precognition validates fields as a user enters data. It is available only in HTMX mode.

## Prerequisites

- **HTMX mode:** Set the DreamForm mode to `htmx` and install HTMX 4. See the [HTMX support guide](../developers/htmx-support).

## Configuration

Enable Precognition in your config:

```php
// site/config/config.php

return [
  'tobimori.dreamform' => [
    'mode' => 'htmx',
    'precognition' => true,
  ],
];
```

## How it works

- Fields validate automatically as the user enters or changes values.
- Text fields wait 500 milliseconds after input stops.
- Select, radio, and checkbox fields validate immediately.
- DreamForm validates all fields of the current step, but only returns the field that caused the request.
- Precognitive validation does not complete the form submission.
- DreamForm uses the `outerMorph` swap of HTMX to keep input focus.

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
