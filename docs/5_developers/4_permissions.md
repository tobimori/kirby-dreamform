---
title: Permissions
description: Managing user access to forms and submissions
---

DreamForm includes a comprehensive permissions system to control user access to forms and submissions. You can configure permissions in user blueprints to restrict access based on user roles.

DreamForm extends Kirby's native permissions system with its own `tobimori.dreamform` category. You set these permissions in your user blueprints, the same way as Kirby's core permissions. Read more about them in the [Kirby permissions guide](https://getkirby.com/docs/guide/users/permissions).

All permissions are enabled by default. Set a permission to `false` to remove it from a user role. This is a list of all available permissions:

```yaml
# site/blueprints/users/editor.yml

title: Editor
permissions:
  tobimori.dreamform:
    accessForms: true
    createForms: true
    updateForms: true
    deleteForms: true
    duplicateForms: true
    changeFormTitle: true
    changeFormStatus: true
    changeFormSlug: true
    accessSubmissions: true
    deleteSubmissions: true
```

| Permission          | Controls                                                                                                                                                   |
| ------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `accessForms`       | Access to the forms page, the forms and their submissions. Without it, the "Forms" menu item is hidden and the DreamForm form field shows no form picker. |
| `createForms`       | Create new forms.                                                                                                                                          |
| `updateForms`       | Edit the content of a form, such as its fields, workflow and settings.                                                                                    |
| `deleteForms`       | Delete forms.                                                                                                                                              |
| `duplicateForms`    | Duplicate forms.                                                                                                                                           |
| `changeFormTitle`   | Rename forms.                                                                                                                                              |
| `changeFormStatus`  | Change the status of forms, e.g. from draft to active.                                                                                                    |
| `changeFormSlug`    | Change the URL slug of forms.                                                                                                                              |
| `accessSubmissions` | View submissions. Without it, the submission tabs on the forms page and on each form are hidden, and the user cannot mark submissions as spam or ham or run their actions again. |
| `deleteSubmissions` | Delete submissions.                                                                                                                                        |

To view submissions, a user needs both `accessForms` and `accessSubmissions`.

These permissions can only restrict access. Kirby still checks its core page permissions and the blueprint options. For example, `pages.create: false` also prevents a user from creating forms.
