---
title: Data Retention
description: Control which submission data DreamForm stores and delete old submissions
---

## Disable saving submissions

You can disable saving submissions as content pages globally using the `storeSubmissions` option in `config.php` or with the **Store Submissions** toggle on the Settings tab inside the Form editor in the panel. If you disable the option globally, the panel does not show the toggle.

```php
// site/config/config.php

return [
  'tobimori.dreamform' => [
    'storeSubmissions' => false,
  ],
];
```

Although there can be a few disadvantages to this, such as:

- Uploaded files are not saved. The [Email action](../actions/email) can still attach files that are uploaded in the last step of the form.
- [Partial submissions](./precognition#partial-submissions) are not available.
- The **Run workflow in background** option is not available.

Additionally, please check with the author of a third-party integration to see if they rely on the submission data being saved if you're using such.

## Temporary submission data

Disabling saving submissions does not remove all traces of the submission data from the server. While a submission is in progress, DreamForm keeps its data in temporary storage:

- In `prg` mode, DreamForm stores the data in the Kirby session. After the submission is finished, DreamForm removes the data when the next page renders, usually the redirect target. If no page loads, the data stays until the session expires.
- In `api` mode and for HTMX requests, DreamForm stores the data in the Kirby cache `tobimori.dreamform.sessionless` for up to 24 hours. DreamForm does not remove it earlier when the submission is finished.

[Multi-step forms](./multi-step) keep the data of all completed steps in this storage until the user submits the last step.

## Deleting submissions regularly

DreamForm does not include a feature to delete submissions automatically. Saved submissions are pages inside the forms page, so you can delete old submissions with a custom [Kirby CLI](https://github.com/getkirby/cli) command:

```php
// site/commands/submissions/cleanup.php

use Kirby\Filesystem\Dir;
use tobimori\DreamForm\DreamForm;

return [
  'description' => 'Deletes DreamForm submissions older than a number of days',
  'args' => [
    'days' => [
      'longPrefix' => 'days',
      'description' => 'Minimum age of the submissions in days',
      'defaultValue' => 30,
      'castTo' => 'int',
    ],
  ],
  'command' => function ($cli) {
    $threshold = strtotime('-' . $cli->arg('days') . ' days');
    $forms = DreamForm::findPageOrDraftRecursive(DreamForm::option('page'));
    $deleted = 0;

    foreach ($forms->index(true)->filterBy('intendedTemplate', 'submission') as $submission) {
      if ((int)$submission->sortDate() < $threshold) {
        Dir::remove($submission->mediaRoot());
        Dir::remove($submission->root());
        $deleted++;
      }
    }

    $cli->success("Deleted {$deleted} submissions");
  },
];
```

`sortDate()` returns the time when the submission started as a Unix timestamp. The command also deletes partial and spam submissions, and the uploaded files of the submissions. It removes the folders directly, because `$submission->delete()` fails on the command line: the `page.delete:before` hook of DreamForm resets the impersonated user.

Run the command with a cron job, for example every night at 3 AM for submissions older than 90 days:

```bash
0 3 * * * cd /var/www/example.com && kirby submissions:cleanup --days 90
```
