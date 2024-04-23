![Kirby DreamForm Banner](.github/banner.png)

# Kirby DreamForm

DreamForm is an opiniated form builder plugin for [Kirby CMS](https://getkirby.com/) that makes forms work like magic.

---

Create powerful single or multi-step forms with a Layout builder directly inside your panel. Create complex submission behaviour workflows with actions. DreamForm supports numerous built-in fieldtypes & actions, but can be expanded and customized as easily as Kirby itself.

Read more about DreamForm on the [official plugin website](https://plugins.andkindness.com/dreamform).

## Comparison

| _Feature Comparison_                                            | DreamForm | [Form Block Suite](https://git.new/k/form-block-suite) | [Uniform](https://git.new/k/uniform)<sup>1</sup> |
| --------------------------------------------------------------- | --------- | ------------------------------------------------------ | ------------------------------------------------ |
| Pricing (per license/site)                                      | 45 €      | 25 US$                                                 | Free                                             |
|                                                                 |           |                                                        |                                                  |
| Single-step forms                                               | ✅        | ✅                                                     | ✅                                               |
| Multi-step forms                                                | ✅        |                                                        |                                                  |
|                                                                 |           |                                                        |                                                  |
| **Create custom forms**                                         | ✅        | ✅                                                     | ✅                                               |
| ...with Blocks in the panel                                     | ✅        | ✅                                                     |                                                  |
| ...with Layouts (multi-column) in the panel                     | ✅        |                                                        |                                                  |
|                                                                 |           |                                                        |                                                  |
| **Customize behaviour after submission**                        | ✅        | ✅                                                     | ✅                                               |
| ...in the panel                                                 | ✅        |                                                        |                                                  |
| ...using hooks                                                  | ✅        | ✅                                                     |                                                  |
|                                                                 |           |                                                        |                                                  |
| Use with JavaScript (API submission)                            | ✅        | ✅                                                     | ✅                                               |
| Use without JavaScript (Form submission)                        | ✅        |                                                        | ✅                                               |
| First-party HTMX support                                        | ✅        |                                                        |                                                  |
|                                                                 |           |                                                        |                                                  |
| **_Built-in fields_**                                           |           |                                                        |                                                  |
| Text                                                            | ✅        | ✅                                                     | <sup>2</sup>                                     |
| Multi-line text                                                 | ✅        | ✅                                                     | <sup>2</sup>                                     |
| Email                                                           | ✅        | ✅ _(using Input)_                                     | <sup>2</sup>                                     |
| Number                                                          | ✅        | ✅ _(using Input)_                                     | <sup>2</sup>                                     |
| Select                                                          | ✅        | ✅                                                     | <sup>2</sup>                                     |
| Radio                                                           | ✅        | ✅                                                     | <sup>2</sup>                                     |
| Checkboxes                                                      | ✅        | ✅                                                     | <sup>2</sup>                                     |
| File uploads                                                    | ✅        | ✅                                                     | <sup>2</sup>                                     |
|                                                                 |           |                                                        |                                                  |
| **_Built-in actions_**                                          |           |                                                        |                                                  |
| Email                                                           | ✅        | ✅                                                     | ✅                                               |
| Redirect                                                        | ✅        | ✅                                                     | ✅                                               |
| Abort                                                           | ✅        |                                                        | ✅                                               |
| Webhook                                                         | ✅        |                                                        | ✅                                               |
| Conditional Action                                              | ✅        |                                                        |                                                  |
| [Buttondown](https://buttondown.email/)                         | ✅ _1.1+_ |                                                        |                                                  |
| [Discord](https://discord.com)                                  | ✅        |                                                        |                                                  |
| [Mailchimp](https://mailchimp.com/)                             | ✅ _1.2+_ |                                                        |                                                  |
|                                                                 |           |                                                        |                                                  |
| **_Built-in guards_**                                           |           |                                                        |                                                  |
| [Akismet](https://akismet.com/)                                 | ✅ _1.1+_ |                                                        |                                                  |
| [CSRF](https://owasp.org/www-community/attacks/csrf)            | ✅        |                                                        | ✅                                               |
| IP-based Rate limiting                                          | ✅        |                                                        |                                                  |
| Honeypot                                                        | ✅        | ✅                                                     | ✅                                               |
| [Turnstile Captcha](https://cloudflare.com/products/turnstile/) | ✅        |                                                        | via third-party plugin                           |
|                                                                 |           |                                                        |                                                  |
| **_Other_**                                                     |           |                                                        |                                                  |
| Re-use forms in different places                                | ✅        | _Each block is a unique form_                          |                                                  |
| Mark submissions for spam                                       | ✅ _1.1+_ |                                                        |                                                  |
| Stores submissions in the panel                                 | ✅        | _Sent emails will be stored_                           |                                                  |
| Permissions for limiting access                                 | ✅        |                                                        |                                                  |
| Gravatar integration                                            | ✅        |                                                        |                                                  |
| Disposable & invalid email domain check                         | ✅ _1.1+_ |                                                        |                                                  |

<small id="1"><sup>1</sup> Technically, you can built most of the features that DreamForm offers yourself using Uniform. For a fair comparison, this table only includes features that can be solved by copying code from the official docs & without writing custom code. </small>

<small id="2"><sup>2</sup> Since Uniform focuses on form validation, all fields are technically possible, but none ship with pre-built HTML snippets. </small>

## License

Kirby DreamForm is not free software. In order to run it on a public server, you'll have to purchase a valid Kirby license & a [valid DreamForm license](https://plugins.andkindness.com/dreamform/pricing).

Copyright 2024 © Tobias Möritz - Love & Kindness GmbH

---

The plugins' name is a homage to Kirby's Dream Land.
