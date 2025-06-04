---
title: Custom Success Page
description: Customizing the thank you message after form submission
---

When editing a form, you can customize the "Thank you" message after switching to the "Settings" tab. However, if a simple text field isn't enough for your case, read on.

## Customizing the blueprint

To customize the blueprint, create a new file in your blueprints folder: `site/blueprints/dreamform/fields/success.yml`

The first line of the blueprint should always be `fields:`. After that, you can add any fields you want, the same way you know from your Kirby blueprints!

```yaml
fields:
  successMessage:
    label: Success Message
    type: writer
  successShowIcon:
    label: Show checkmark icon?
    type: toggle
```

You should make sure that the keys are not already in use. We recommend to prefix every field with `success` to avoid collission.

## Customizing the snippet

To actually use the set field values in your forms, you have to override the included success snippet. To get going, create a new snippet file at `site/snippets/dreamform/success.php`.

This file will be automatically output by the default form snippet when the submission was successful. It's important to keep in mind, that you'll only have access to `$kirby`, `$site`, `$page`, `$form` as well as the `$attr` array that includes assigned classes & attributes. - but no other custom variables you might be sending with your `snippet('dreamform/form')` call.

The default snippet looks something like this - mix it up and change it the way you need. You have access to all fields set above with the `$form` variable, e.g. `$form->successShowIcon()->toBool()`.

```php
<?php

/**
 * @var \Kirby\Cms\Page $page
 * @var \tobimori\Dreamform\Models\FormPage $form
 * @var array|null $attr
 */ ?>

<div <?= attr($attr['success']) ?>>
  <?= $form->successMessage() ?>
</div>
```