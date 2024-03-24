## Styling

By default, DreamForm comes entirely unstyled. You can attach custom classes to all elements in the form using some options.

### Rendering the snippet

You can pass an array of attributes to the `attr` option in the snippet for attaching classes to elements. The array can have any of the following keys:

```php
<?php snippet('dreamform/form', [
	'form' => $page->myForm()->toPage(),
	'attr' => [
		// General attributes
		'form' => ['class' => 'form'],
		'row' => [],
		'column' => [],
		'field' => [],
		'label' => [],
		'error' => [],
		'input' => [],
		'button' => [],

		// Field-specific attributes
		'textarea' => [
			'field' => [],
			'label' => [],
			'error' => [],
			'input' => [],
		],
		'text' => [
			'field' => [],
			'label' => [],
			'error' => [],
			'input' => [],
		],
		'select' => [
			'field' => [],
			'label' => [],
			'error' => [],
			'input' => [],
		],
		'number' => [
			'field' => [],
			'label' => [],
			'error' => [],
			'input' => [],
		],
		'file' => [
			'field' => [],
			'label' => [],
			'error' => [],
			'input' => [],
		],
		'email' => [
			'field' => [],
			'label' => [],
			'error' => [],
			'input' => [],
		],
		'radio' => [
			'field' => [],
			'label' => [],
			'error' => [],
			'input' => [],
			'row' => []
		],
		'checkbox' => [
			'field' => [],
			'label' => [],
			'error' => [],
			'input' => [],
			'row' => []
		],

		'success' => [], // Success message
		'inactive' => [], // Inactive message
	]
]); ?>
```

If field-specific attributes are set, they will override the general attributes. You can generally omit any key if you don't want to attach any classes to it.

### HTML Structure

A simplified example of the default HTML structure would be:

```php
<form>
	<error data-error="true">
	<row>
		<column style="grid: half;">
			<field data-has-error>
				<label>
				<input>
				<error data-error="key">
			</field>
			<field>
				<label>
				<textarea>
				<error data-error="key">
			</field>
		</column>
		<column style="grid: half;">
			<field>
				<label>
				<select>
				<error data-error="key">
			</field>
			<field>
				<row>
					<input type="radio">
					<label>
				</row>
				<row>
					<input type="radio">
					<label>
				</row>
				<row>
					<input type="radio">
					<label>
				</row>
				<error data-error="key">
			</field>
		</column>
	</row>
	<row>
		<column style="grid: full;">
			<field>
				<button>
			</field>
		</column>
	</row>
</form>
```

You can partially override snippets to change the structure of the form. Take a look at the plugin's `snippets` folder to see what you can override.
