---
title: Going Live
description: Checklist for deploying DreamForm to production
---

This page contains a checklist of important considerations when deploying DreamForm to production environments. Check each item to give your users and editors the best experience.

## Fields

Make sure that all available fields have a snippet and fit the style of the rest of your form. See [Styling](./styling) for more information.

Disable the fields that your project does not need with the `fields.available` option. This way, editors do not see options that do not work or that they do not need. See [What are fields?](../fields/what-are-fields) for the option and the list of fields.

## Actions

Disable the actions that your project does not need with the `actions.available` option. See [What are actions?](../actions/what-are-actions) for the option and the list of actions.

Make sure that all actions that use third-party services are correctly configured and work. Some actions, such as Mailchimp, Brevo, Buttondown, Loops and MailerLite, are only available in the panel when you set an API key. Mailchimp, Buttondown and Loops also check the key with their API. Plausible needs a domain and the `ip` and `userAgent` metadata. If an action is not available on the production server, for example because the server cannot reach the API, DreamForm skips its blocks without an error.

The [Email action](../actions/email) sends emails with the email transport of your Kirby installation. Make sure that your production SMTP credentials are correct and send a test submission.

## Guards

Make sure that the guards you use work with your stack. Test your forms in an environment that is similar to production.

- The [CSRF guard](../guards/csrf) is enabled by default. CSRF tokens are unique to the user session, so they do not work on cached pages. If you cache pages that contain forms, disable the CSRF guard.
- The [Rate Limit guard](../guards/ratelimit) uses the IP address that Kirby gets from `REMOTE_ADDR`. Behind a CDN or reverse proxy, this is the IP address of the proxy, so all visitors share one rate limit. Configure your web server to set `REMOTE_ADDR` to the real client IP, or do not use this guard.

## Submission modes

### PRG mode

In PRG mode, DreamForm stores the submission state in the Kirby session and shows errors or the success message after the redirect. The Kirby pages cache works with forms: when it is active, DreamForm adds a query string to the redirect, and Kirby does not cache requests with query strings or that use the session. Static or CDN caches that ignore query strings, such as Staticache, must exclude pages with forms. Otherwise, the results of a submission do not show correctly.

### HTMX mode

In HTMX mode, forms use a normal PRG submission when JavaScript is not available. This fallback has the same cache limitations. See [HTMX Support](./htmx-support) for more limitations.

### API mode

In API mode, DreamForm returns the submission state as JSON, with the status code `200` for a successful submission and `400` for a failed submission. Make sure that your script shows errors and the success message to the user.

For multi-step forms, your script must also send the session token and the correct step with each request. See [Multi-step forms](../general/multi-step).

### Encryption secret

HTMX and API modes require either `tobimori.dreamform.secret` or an explicitly configured Kirby `content.salt`. Make sure that this value is set on your production server. Do not commit it to your repository. See [Installation](../setup/installation) for more information.

## Debug mode

The `debug` option shows sensitive errors on form submission. By default, it uses the value of the Kirby `debug` option. Make sure that debug mode is disabled in production.

## License

Without a valid DreamForm license, the site is in demo mode. In demo mode, only Panel admins can submit forms. This limit does not apply to local environments. Activate your license in the Panel on the production domain.

## Custom code

If you use custom guards, fields, actions or other custom logic, make sure that you test them and that they work as expected. See [Custom guards](../guards/custom), [Custom fields](../fields/custom) and [Custom actions](../actions/custom).

## Get help

If you have a problem, open an issue on the [GitHub repository](https://github.com/tobimori/kirby-dreamform/issues) or ask for help on the [Kirby Discord server](https://chat.getkirby.com).
