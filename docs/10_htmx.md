## HTMX support

[HTMX](https://htmx.org/) is a simple and lightweight JavaScript library that allows you to simplify AJAX calls & page updates. Instead of JSON responses, HTMX uses HTML fragments to update the page.

DreamForm thinks of HTMX as a first-class citizen. With a simple config change, you can progressively enhance your forms to use HTMX. If the form is submitted without JavaScript, it will work the same way as with the browser-native form submission.

### Getting started

1. Follow the [Installing](https://htmx.org/docs/#installing) docs of HTMX to include the library in your project.

2. In your `config.php`, set the `tobimori.dreamform.mode` option to `htmx`.

```php
<?php
// site/config/config.php

return [
	'tobimori.dreamform.mode' => 'htmx'
];
```

3. That's it! Your forms will now output the necessary attributes to work with HTMX - even if the Kirby cache is enabled.

### Limitations

HTMX requires you to use the default snippets or a minimally derived version available under the same name (`dreamform/form`). This is because a HTMX request doesn't render the whole page, but only the form itself. Also keep in mind that, there's currently no way to access custom arguments in the form snippet, since they will be lost in subsequent requests.
