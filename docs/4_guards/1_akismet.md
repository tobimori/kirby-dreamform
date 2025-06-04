---
title: Akismet
description: Spam filtering with Akismet API
---

Akismet is a Spam filtering platform commonly known from the WordPress universe (and operated by its parent company). Its API can be easily integrated in third party platforms like Kirby and is also used in other apps such as Discourse (the Kirby forum software).

Keep in mind that you need an [active Akismet subscription](https://akismet.com/pricing/) to use it. Akismet is free for personal, non-commercial sites.

A spam submission marked by Akismet will not stop the submission. Instead, it will be stored as Spam submission and actions can be run later.

## Adding Akismet

Get started by copying your API key from the [Akismet account page](https://akismet.com/account/). Proceed by adding the guard as well as the API key to the config:

```php
// site/config/config.php

return [
  'tobimori.dreamform' => [
    'guards' => [
      'available' => ['akismet', /* other guards here */ ],
      'akismet' => [
        'apiKey' => fn () => env('AKISMET_API_KEY')
      ]
    ],
  ],
];
```

Ideally, you should not commit these keys to your repository, but instead load it from an environment variable, e.g. using the [kirby-dotenv plugin by Bruno Meilick](https://github.com/bnomei/kirby3-dotenv), as shown in the example above.

Since Akismet requires the IP address and user agent of the sender, we have to configure the plugin to collect the data as well.

```php
// site/config/config.php

return [
  'tobimori.dreamform' => [
    'guards' => [ /* [...] */ ],
    'metadata' => [
        'collect' => [
            'ip', 
            'userAgent'
        ]
    ],
  ],
];
```

Try testing the guard by sending a submission to a form with an `email` field and the email `akismet-guaranteed-spam@example.com`.

## Options

| Option | Default | Accepts | Description |
| --- | --- | --- | --- |
| tobimori.dreamform.guards.akismet.apiKey | `null` | `string|callback` | The Akismet API key from your account page |
| tobimori.dreamform.guards.akismet.fields.comment_author | `[...]` | `array` | Fields to use for the author name param |
| tobimori.dreamform.guards.akismet.fields.comment_author_email | `[...]` | `array` | Fields to use for the email param |
| tobimori.dreamform.guards.akismet.fields.comment_author_url | `[...]` | `array` | Fields to use for the url/website param |
| tobimori.dreamform.guards.akismet.fields.comment_content | `[...]` | `array` | Fields to use for the content param |