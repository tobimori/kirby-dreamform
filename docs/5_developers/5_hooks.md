---
title: Hooks
description: Extending DreamForm with custom hooks
---

DreamForm utilizes custom hooks to allow you to add your own functions when certain events happen. You can read more about hooks in the [Kirby documentation](https://getkirby.com/docs/reference/plugins/extensions/hooks).

However, even if hooks are available in DreamForm, it's recommended to use custom fields, actions & guards to extend DreamForm instead. Hooks should be a last resort if you can't achieve what you want with the available options.

## `dreamform.submit:before`

This hook is called before a form submission is processed.

```php
return [
  'hooks' => [
    'dreamform.upload:before' => function (\tobimori\DreamForm\Models\SubmissionPage $submission, \tobimori\DreamForm\Models\FormPage $form) {
      // your code goes here
    }
  ]
];
```

## `dreamform.submit:after`

This hook is called after a form submission has been processed.

```php
return [
  'hooks' => [
    'dreamform.upload:after' => function (\tobimori\DreamForm\Models\SubmissionPage $submission, \tobimori\DreamForm\Models\FormPage $form) {
      // your code goes here
    }
  ]
];
```

## `dreamform.upload:before`

This hook is called before any file from a file upload field is processed.

```php
return [
  'hooks' => [
    'dreamform.upload:before' => function (array $file, \tobimori\DreamForm\Fields\FileUploadField $field) {
      // your code goes here
    }
  ]
];
```

The `$file` array consists of the same content you'd receive from the [PHP $_FILES array](https://www.php.net/manual/en/reserved.variables.files.php)

```php
[
  "name" => "MyFile.jpg",
  "type" => "image/jpeg",
  "tmp_name" => "/tmp/php/php6hst32",
  "error" => UPLOAD_ERR_OK,
  "size" => 98174
]
```

## `dreamform.upload:after`

This hook is called after any file from a file upload field has been processed and is created as a file of the submission page.

```php
return [
  'hooks' => [
    'dreamform.upload:after' => function (\Kirby\Cms\File $file, \tobimori\DreamForm\Fields\FileUploadField $field) {
      // your code goes here
    }
  ]
];
```