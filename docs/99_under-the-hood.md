## Under the hood

This document explains the inner workings of DreamForm.

### Lifecycle of a form submission

0. The form page is resolved, e.g. from a field.

1. The form template accesses the submission from the session data, if available.

2. The form is rendered in the template, either using the default snippet or a custom one.

3. The user fills out the form and submits it.

4. The submission page is created (virtually).

5. The guards are checked.

6. The form fields are validated.

7. If the form validation was successful & the form is at the last stage, the form actions are executed.

8. The form submission is saved as session data and as page if the action execution was successful.

### Performer

Actions & Guards are both Performers. Guards are called before the form validation, while actions are called after the form validation.
