---
title: Installation
description: Get started with using DreamForm
---

## Requirements

- Kirby 5.0+ or later
- PHP 8.3+ or later
- A valid Kirby license & DreamForm license
- UUIDs enabled in your Kirby installation

### Compatibility with other plugins

If you're using any of the following plugins, make sure you're at least on the specified version to make them work with DreamForm.

- Blade Templates by Lukas Leitsch: Version 3.0.1+ or later
- Twig Templates by JUST: Version 5.0.2+ or later
- Kirby SEO: Version 1.0.0+ or later

## Installation

**Composer is the recommended way to install DreamForm.** Run the following command in your terminal:

```bash
composer require tobimori/kirby-dreamform
```

While we recommend using Composer, some sites decide do not use it. In this case you can download and copy this repository to `/site/plugins/dreamform`, or apply [this repository](https://github.com/tobimori/kirby-dreamform) as Git submodule.

After opening the panel, the plugin should automatically create the "forms" page that houses all forms.

### Manual Installation

If you want to customize the path your forms folder is stored in, you can also choose to create the form automatically, by following the steps:

1. Go into your sites' `content` folder, and create a new folder called `forms`
2. Inside `/forms` create a file named `forms.txt` or `forms.en.txt` if you're using a multi-lang setup, respectively. If your default language is different than `en`, replace the respective code in the file name.
3. Open the file in a editor of your choice, and add the following content:

```yaml
Uuid: forms
```

## Encryption secret

**If you want to use the HTMX or API submission mode,** it is required to specify a secret used for encrypting certain variables that will be output on the template in plain text.

**You should not commit this secret to your repository,** but instead load it from an environment variable or a secrets file. My recommendation would be using the kirby-dotenv plugin by Bruno Meilick like this:

```php
// site/config/config.php

return [
  'tobimori.dreamform' => [
    // encryption secret for htmx attributes
    'secret' => fn () => env('DREAMFORM_SECRET')
  ],
];
```

You can generate a good random secret using the OpenSSL CLI and the following command:

```bash
openssl rand -base64 32
```

## Adding Forms to the Panel sidebar

If you want to add the Forms entry to the menu sidebar, you can do so without writing complicated functions yourself using the provided `Menu` helper class in the `config.php` file. An example configuration could look like this:

```php
// site/config/config.php

use tobimori\DreamForm\Support\Menu;

return [
  // Custom menu to show forms in the panel sidebar
  'panel.menu' => fn () => [
    'site' => Menu::site(),
    'forms' => Menu::forms(),
    'users',
    'system',
    // [...]
  ],
];
```
