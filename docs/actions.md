# Actions

## What are Actions?

Actions are ran after form submission & field validation. They can utilize the submitted field data, change them, or ultimately still reject the submission.

By default, Kirby Dream Form includes the following Actions:

- Email Action
  > Sends an email to any specified email address with the submitted field data
- Redirect Action
  > Redirects the user to a specified URL after all actions are ran
- Conditional Action
  > Execute nested actions based on a condition
- Abort Action
  > Rejects the submission with a custom error message
- Webhook Action
  > Sends the submitted field data via POST rquest to a specified URL

Combining these actions, you can create complex, multi-dimensional workflows for your forms, without leaving the Kirby Panel.
