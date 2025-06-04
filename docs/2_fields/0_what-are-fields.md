---
title: What are Fields?
description: Learn about the core building blocks of your forms
---

Fields are the core building blocks of your form. They define what data, how it can be submitted, as well as validate & sanitize it.

A field doesn't have to be a form input, it can also be a button or an image, although they will always follow the same syntax as a normal field.

## Available Fields

### Built-in Fields

* [Button](1_button.md) - Button field for form navigation and submission
* [Text](2_text.md) - Single-line text input field
* [Multi-line Text](3_multi-line-text.md) - Multi-line text input field for longer content
* [Number](4_number.md) - Number input field for numeric values
* [Email](5_email.md) - Email input field with validation
* [Checkboxes](6_checkboxes.md) - Multiple choice selection field
* [Radio](7_radio.md) - Single choice selection field
* [Select](8_select.md) - Dropdown selection field
* [Pages](9_pages.md) - Dropdown populated with Kirby pages
* [Hidden](10_hidden.md) - Hidden field for URL parameters and data storage
* [File Upload](11_file-upload.md) - File upload field for documents and media

### Fields created by the Community

* [Date Field using Air DatePicker by Tobias Möritz](https://github.com/tobimori/dreamform-date-field)
* [Date Field by Till Schander](https://github.com/tillschander/dreamform-datefield)
* [Info Field by Moinframe](https://github.com/moinframe/kirby-dreamform-info-field)

### Creating Custom Fields

* [Custom Fields](12_custom.md) - Learn how to create your own custom fields

## Configuring Fields

By default, all registered and configured fields are available, but you can customize the available fields in your `config.php`.

```php
// site/config/config.php

return [
  'tobimori.dreamform' => [
    'fields' => [
      'available' => ['text', 'textarea', 'select', 'checkboxes', /* other guards here */ ],
    ],
  ],
];
```

### Options

| Option | Default | Accepts | Description |
| --- | --- | --- | --- |
| tobimori.dreamform.fields.available | `true` | `boolean|array` | Available fields, true enables all fields |
