---
title: What are Actions?
description: Learn about actions that run after form submission
---

Actions are ran after form submission & field validation. They can utilize the submitted field data, add new data or ultimately still reject the submission.

Combining these actions, you can create complex, multi-dimensional workflows for your forms, without leaving the Kirby Panel.

## Available Actions

### Built-in Actions

* [Abort](1_abort.md) - Abort action to stop form processing
* [Conditional](2_conditional.md) - Execute actions based on field conditions
* [Discord](3_discord.md) - Send form submissions to Discord via webhooks
* [Email](4_email.md) - Send email notifications with form data
* [Redirect](5_redirect.md) - Redirect users after form submission
* [Webhook](6_webhook.md) - Send form data to third-party services via webhooks
* [Buttondown](7_buttondown.md) - Subscribe users to Buttondown newsletters
* [MailerLite](9_mailerlite.md) - Create or update MailerLite subscribers

### Creating Custom Actions

* [Custom Actions](8_custom.md) - Learn how to create your own custom actions

### Actions created by the community

* None

## Configuring Actions

By default, all registered and configured actions are available, but you can customize the available actions in your `config.php`.

```php
// site/config/config.php

return [
  'tobimori.dreamform' => [
    'actions' => [
      'available' => ['abort', 'conditional', 'redirect', 'webhook', /* other actions here */ ],
    ],
  ],
];
```

### Options

| Option | Default | Accepts | Description |
| --- | --- | --- | --- |
| tobimori.dreamform.actions.available | `true` | `boolean|array` | Available actions, true enables all actions |
