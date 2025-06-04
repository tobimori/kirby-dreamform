---
title: Precognition
description: Real-time field validation with HTMX
---

Precognition enables real-time field validation as users type, providing immediate feedback without requiring a full form submission. This feature is only available when using HTMX mode.

## Prerequisites

1. **HTMX Mode**: Your form must use HTMX mode
2. **Idiomorph Extension**: Required for DOM morphing

Install [HTMX](https://htmx.org/) and the [Idiomorph extension](https://htmx.org/extensions/idiomorph/).

## Configuration

Enable precognition in your config:

```php
// site/config/config.php

return [
  'tobimori.dreamform' => [
    'mode' => 'htmx',
    'precognition' => true,
  ],
];
```

## How It Works

- Fields validate automatically as users type or change values
- Text fields wait 500ms after typing stops before validating
- Select/radio/checkbox fields validate immediately on change
- Only the changed field is validated, not the entire form
- No data is saved during precognitive validation

## Partial Submissions

When precognition is enabled, you can also enable partial submissions:

```php
// site/config/config.php

return [
  'tobimori.dreamform' => [
  	// ...
    'partialSubmissions' => true,
  ],
];
```
This automatically saves each field's value as the user fills it out. This helps track progress for abandoned forms and prevents data loss from failed submissions.
