title: Cap
description: Cap proof-of-work captcha integration
---

DreamForm has built-in support for [Cap](https://capjs.js.org/), a lightweight, privacy-friendly CAPTCHA that uses SHA-256 proof-of-work instead of tracking or fingerprinting. It’s fast, self-hostable, and easy to integrate.

## Adding Cap

Follow the official Cap docs to deploy a Cap server (Node/Bun/Deno or standalone Docker) and create your keys. Then add the guard and required options to your `config.php`.

```php
// site/config/config.php

return [
  'tobimori.dreamform' => [
    'guards' => [
      'available' => ['cap', /* other guards here */],
      'cap' => [
        // Base API endpoint of your Cap server (https://<instance_url>/<site_key>/)
        'endpoint' => fn () => env('CAP_ENDPOINT'),
        // Server secret used to verify the token server-to-server
        'secretKey' => fn () => env('CAP_SECRET_KEY'),
        // Automatically inject the widget script
        'injectScript' => true,
        // Load widget from your Cap server's asset endpoint instead of the CDN
        'useAssetServer' => false,
      ],
    ],
  ],
];
```

Ideally, you should not commit these keys to your repository, but instead load them from environment variables, e.g. using the [kirby-dotenv plugin by Bruno Meilick](https://github.com/bnomei/kirby3-dotenv), as shown in the example above.

## How it works

- When a form includes the `cap` guard, DreamForm renders a `<cap-widget>` element.
- The widget obtains a challenge from your Cap server and, after solving the PoW, writes a token into the form as `cap-token`.
- On submit, DreamForm verifies the token against your Cap server using the configured `endpoint` and `secretKey`.

Cap supports customization via CSS variables, among other features. See the official docs for details: [Cap documentation](https://capjs.js.org/guide/).

Note: If you use HTMX, the integration automatically resets the widget between swaps for a clean state.

## Options

| Option | Default | Accepts | Description |
| --- | --- | --- | --- |
| tobimori.dreamform.guards.cap.endpoint | `null` | `string|callable` | Base API endpoint of your Cap server ( `https://<instance_url>/<site_key>/`). |
| tobimori.dreamform.guards.cap.secretKey | `null` | `string|callable` | Secret key used by DreamForm to verify tokens with your Cap server. |
| tobimori.dreamform.guards.cap.injectScript | `true` | `boolean` | Inject the client-side widget script automatically. Falls back to CDN `https://cdn.jsdelivr.net/npm/@cap.js/widget`. |
| tobimori.dreamform.guards.cap.useAssetServer | `false` | `boolean` | If `true`, load the widget from your Cap server at `https:///<instance_url>/widget.js` based on the configured endpoint. |
