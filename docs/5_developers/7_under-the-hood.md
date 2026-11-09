---
title: Under the Hood
description: Technical details about how DreamForm works
---

This page explains the inner workings of DreamForm. It is useful when you extend DreamForm with custom guards, fields, or actions, or when you use [hooks](../5_developers/5_hooks.md).

## Request handling

A form submits to the URL of its form page. When the form page receives a `POST` request, it calls `FormPage::submit()`. If the URL has the `?precognition=true` query parameter, DreamForm runs the precognition lifecycle instead of the regular one.

After the submission is processed, the response depends on the `mode` option:

- **`prg`:** DreamForm redirects to the referring page. If the submission was successful and a redirect URL is set, it redirects to that URL.
- **`htmx`:** DreamForm returns the rendered form snippet. If a redirect URL is set, it sends an `HX-Redirect` header. Requests without HTMX use the `prg` flow.
- **`api`:** DreamForm returns the submission state as JSON, with status `200` for a successful submission and `400` for a failed one.

## Submission lifecycle

### Regular submission

1. **Submission initialization:** DreamForm gets the submission from the session. If there is no submission, or the submission belongs to a different form, DreamForm creates a new virtual submission page.
2. **`dreamform.submit:before` hook:** The hook runs before all other steps. It can return a modified submission.
3. **Metadata collection:** DreamForm collects the data points from the `metadata.collect` option, for example the IP address and the user agent.
4. **Guards:** DreamForm calls `run()` on each guard.
5. **Field validation:** DreamForm sets the values of the fields in the current step from the request and validates them. Failed fields add an error to the submission state. On the final step, DreamForm shows an error if all fields are empty and the step has no required fields.
6. **Post-validation guards:** DreamForm calls `postValidation()` on each guard. Spam guards use this step, for example to mark the submission as spam.
7. **Actions:** The actions run only on the final step, and only if the submission is successful and not spam. Actions run in the order of the workflow. If background processing is enabled, some actions run in a queued job. See [Background actions](#background-actions).
8. **Finalization:** If the submission is successful and this is the final step, DreamForm finishes the submission:
   1. It marks the submission as finished (not partial).
   2. It runs the `dreamform.submit:after` hook. The hook can return a modified submission.
   3. It saves the submission to disk, if storing submissions is enabled and the submission has values.

   On an intermediate step of a multi-step form, DreamForm moves to the next step instead. The `dreamform.submit:after` hook does not run and the submission is not saved here.
9. **After-submit fields:** If the submission is successful, DreamForm calls `afterSubmit()` on each field of the current step. The file upload field uses this to store the uploaded files.
10. **Partial submission storage:** If partial submissions are enabled for the form and in the config, DreamForm saves the submission to disk, also when it is not finished or has errors.
11. **Session storage:** DreamForm always stores the submission in the session at the end. The form template uses it to show errors, values, the current step, or the success message.

### Early exits

Guards and actions can stop the lifecycle with exceptions:

- **`PerformerException` (from `cancel()`):** The lifecycle stops. DreamForm sets the form error. Users only see the message passed to `cancel()` if it is called with `public: true` or in debug mode; otherwise they see a generic error. DreamForm saves a partial submission if partial submissions are enabled, and stores the submission in the session. The `dreamform.submit:after` hook does not run and the submission is not saved as finished.
- **Silent `PerformerException` (from `silentCancel()`):** The lifecycle stops, but the user sees the form as successful. DreamForm stores the submission in the session and finishes it. The `dreamform.submit:after` hook runs, but the submission is not saved to disk, except when partial submissions are enabled. In debug mode, a silent exception acts like a normal `PerformerException`.
- **`SuccessException` (from `success()` in an action):** The remaining actions do not run. DreamForm finalizes the submission (step 8), calls the after-submit fields (step 9), and stores the submission in the session.
- **Other exceptions in actions:** DreamForm adds the error to the submission log and continues with the next action.
- **Other exceptions in other steps:** DreamForm sets the exception message as the form error.

If an action throws a `PerformerException` and "Continue submission on error" is enabled for the form, DreamForm logs the error and continues with the next action. When you run the actions again from the Panel, DreamForm forces them, and they also continue after errors.

### Background actions

Actions can run in a queued job when all of these conditions are true:

- The `tobimori\Queues` plugin is installed.
- "Store Submissions" is enabled for the form.
- "Run workflow in background" (`runWorkflowInQueue`) is enabled for the form. It is enabled by default when the plugin is installed.

In this case, DreamForm runs actions that do not support queues immediately, in the same request. These are the actions whose `supportsQueues()` method returns `false`: the abort, conditional, and redirect actions. DreamForm pushes all other actions to one `dreamform-submission` job, with the submission UUID and the action block data.

The `SubmissionJob` class later finds the saved submission by its UUID, creates the actions again, and runs them. Errors from queued actions go to the job log and the submission log. They do not change the response, because the user already received the success message. If your custom action must change the response, for example with a redirect or an error, return `false` from `supportsQueues()`.

### Precognition lifecycle

When [Precognition](../0_general/4_precognition.md) is enabled in HTMX mode, each field sends a request to the form URL with `?precognition=true` when its value changes. The request follows a shorter flow:

1. **Submission initialization:** DreamForm gets the submission from the session or creates a new one, the same as for a regular submission.
2. **Metadata collection:** DreamForm collects the data points from the `metadata.collect` option.
3. **Precognitive guards:** DreamForm calls `precognitiveRun()` on each guard. The default `run()` method of a guard also calls `precognitiveRun()`.
4. **Field validation:** DreamForm sets and validates all fields of the current step, and updates the errors in the submission state.
5. **Partial submission storage:** If partial submissions are enabled, DreamForm saves the submission to disk.
6. **Session storage:** DreamForm stores the submission in the session.
7. **Response:** DreamForm returns only the HTML of the field that sent the request. It finds this field from the `HX-Source` header (HTMX 4) or the `HX-Trigger` header (HTMX 2). If the request did not include a session, the response also includes the session snippet.

Precognitive requests do not run the `dreamform.submit:before` or `dreamform.submit:after` hooks, the post-validation guards, the actions, or the after-submit fields. They do not move to the next step. They also do not finish the submission, except when a guard calls `silentCancel()` in `precognitiveRun()`, as the honeypot guard does. In this case, DreamForm finishes the submission and runs the `dreamform.submit:after` hook.
