# Getting Started (Pre-Alpha)

1. Drag-and-drop the `kirby-dreamform` folder into your `site/plugins` folder.

2. Using your Finder, create a folder `forms` in your `content` folder.

3. Create a new file in the `forms` folder, e.g. `forms.txt` (Add the language suffix if using multi-lang)

4. Add the following content to the file:

```yaml
Uuid: forms
```

5. You should be able to open the forms page in the panel now.

6. Add a 'form' field to any blueprint you wat.

```yaml
fields:
	myForm:
		extends: dreamform/fields/form
```

7. In your template, render the form using the default snippet:

```php
<?php snippet('dreamform/form', [
	'form' => $page->myForm()->toPage()
]); ?>
```

You can also add attributes to some elements for styling (see styling.md).

8. You can now submit the form and see the result in the panel.

If you want to use API submissions, you can set `tobimori.dreamform.mode` to `api` in your `config.php` file.

```php
return [
	'tobimori.dreamform.mode' => 'api'
];
```

(Other options are available as well but not documented yet.)
