---
title: Honeypot
description: Hidden field spam protection
---

The **Honeypot guard** is enabled by default.

A Honeypot is a visually hidden field that looks like a real field to a bot. By default, it looks through a set of keys in the config to find one that is not used within the field. Those might be `website`, `email`, or `url`. Whenever the form field contains any value, the guard will reject the request.

## Disabling the Honeypot guard

You can set the executed guards globally in your `config.php`. You can also customize the available field keys used by the Honeypot field.

```php
// site/config/config.php

return [
  // Settings for the DreamForm plugin
  'tobimori.dreamform' => [
    'guards' => [
      'available' => ['honeypot'],
      'honeypot.availableFields' => ['website', 'email', 'name', 'url', 'birthdate'], 
    ],
  ],
];
```

## Options

| Option | Default | Accepts | Description |
| --- | --- | --- | --- |
| tobimori.dreamform.guards.honeypot.availableFields | `[...]` | `array` | Available field keys for the honeypot field |