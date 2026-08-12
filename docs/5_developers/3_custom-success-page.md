---
title: Custom Success Page
description: Customizing the thank you message after form submission
---

When editing a form, you can customize the "Thank you" message in the "Settings" tab. If the default writer field is not enough, you can replace it with your own fields.

## Customizing the blueprint

To customize the blueprint, create `site/blueprints/dreamform/fields/success.yml` in your blueprints folder.

The first line of the blueprint must be `fields:`. You can then add any number of fields of any Kirby field type, as in a regular Kirby blueprint.

```yaml
fields:
  successMessage:
    label: Success Message
    type: writer
  successShowIcon:
    label: Show checkmark icon?
    type: toggle
```

Make sure that the field names are not already in use. We recommend that you prefix each field name with `success` to prevent collisions.

## Customizing the snippet

To actually use the set field values in your forms, you have to override the included success snippet. To get going, create a new snippet file at `site/snippets/dreamform/success.php`.

The default form snippet automatically renders this file after a successful submission. You have access to `$kirby`, `$site`, `$page`, `$form`, `$submission`, and the `$attr` array, but not to other custom variables passed to `snippet('dreamform/form')`.

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