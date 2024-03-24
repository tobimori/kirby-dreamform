## Hooks

DreamForm utilizes custom hooks to allow you to add your own functions when certain events happen. You can read more about hooks in the [Kirby documentation](https://getkirby.com/docs/reference/plugins/extensions/hooks).

However, even if hooks are available in DreamForm, it's recommended to use custom fields, actions & guards to extend DreamForm instead. Hooks should be a last resort if you can't achieve what you want with the available options.

### dreamform.submit:before

This hook is called before a form submission is processed.

### dreamform.submit:after

This hook is called after a form submission has been processed.

### dreamform.upload:before

This hook is called before a file upload is processed.

### dreamform.upload:after

This hook is called after a file upload has been processed and is created as a file of the submission page.
