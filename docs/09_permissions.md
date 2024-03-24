## Permissions

DreamForm extends Kirby's native permissions system to allow you to control who can edit forms or access submissions. You can set permissions in your user blueprints.

```yaml
# /site/blueprints/users/editor.yml

title: Editor
permissions:
  tobimori.dreamform:
    *: false
    access: true
```

### Available Permissions
