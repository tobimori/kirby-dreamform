---
title: MailerLite
description: Subscribe users to MailerLite newsletters
---

[MailerLite](https://www.mailerlite.com/) is a newsletter platform for creating campaigns, automations and landing pages.

With the MailerLite action, you can connect DreamForm to your MailerLite account and let senders subscribe to your newsletter.

## Get started

Grab an API token from your MailerLite account. Head to **Integrations → MailerLite API**, create a new token and add it to your `config.php` file.

```php
// site/config/config.php

return [
  'tobimori.dreamform' => [
    'actions' => [
      'mailerlite.apiKey' => fn () => env('MAILERLITE_API_KEY')
    ],
  ],
];
```

Ideally, you should not commit this token to your repository. Load it from an environment variable instead, as shown in the example above.

| Option | Default | Accepts | Description |
| --- | --- | --- | --- |
| tobimori.dreamform.actions.mailerlite.apiKey | `null` | `string|callback` | The API token used to communicate with MailerLite |

## Adding the action to your form

If the API token is set, the MailerLite action will show up in the action selector. Add it to your workflow and select **the field that contains the email address** you want to subscribe.

You can also pick one or more MailerLite groups and connect additional fields, such as the sender's name or company, to the matching fields in MailerLite.

If the email address already exists in MailerLite, the existing subscriber will be updated. Fields and groups that are not part of the action will be left untouched.

## Double opt-in

MailerLite handles double opt-in for API subscriptions globally. If you want new subscribers to confirm their email address first, enable **Double opt-in for API and integrations** under **Account settings → Subscribe settings** in MailerLite.

The DreamForm action follows this setting. It will not silently resubscribe contacts who have previously unsubscribed.
