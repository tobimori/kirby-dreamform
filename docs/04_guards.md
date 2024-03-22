## Guards

### What are guards?

Guards are a way to prevent form submission [**before** field validation](/docs/99_under-the-good.md#lifecycle-of-a-form-submission). They are intended for security checks, like CSRF, Honeypot or Captchas.

Conceptually, Guards and Actions are similar. **Unlike** Guards, Actions are ran after form validation & can be configured with form-specific settings through the panel. Guards can be enabled or disabled **globally only** in the config.

By default, CSRF & Honeypot guards are enabled. It is most likely the responsibility of the sites' developer and not meant to be changed by the user.

Some guards might have a "silent" option, which means that they will not show any error messages to the user, but instead show the default success screen, **except** if debug mode is enabled. Keep this in mind when reporting potential issues.

### Available Guards

#### CSRF

The CSRF guard is enabled by default. It checks if the form submission contains a valid CSRF token. If not, the submission will be rejected.

#### Honeypot

The honeypot guard is enabled by default. It checks if the honeypot field is empty. If not, the submission will be rejected.

#### Turnstile (Cloudflare Captcha)

TODO

#### Rate Limit

TODO

### Creating your own guards

TODO
