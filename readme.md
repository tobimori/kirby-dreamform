# Kirby DreamForm ✨

**_Form creation, validation & handling as smooth as a Warp Star Ride._** 💫

Kirby DreamForm is an opiniated plugin for [Kirby CMS](https://getkirby.com/) that allows you to create forms with a layout builder interface. It's easy to expand and customize, and built with Kirby's native field types.

## Sites using DreamForm

- [Vierbeiner in Not](https://www.vierbeinerinnot.de/)
- [Factory Campus](https://factorycampus.de/)
- [Skyflow](https://www.flyskyflow.com/)

## Comparison

| _Feature Comparison_                        | DreamForm | [Form Block Suite](https://github.com/youngcut/kirby-form-block-suite) | [Uniform](https://kirby-uniform.readthedocs.io/en/latest/)<a href="#1"><sup>1</sup></a> |
| ------------------------------------------- | --------- | ---------------------------------------------------------------------- | --------------------------------------------------------------------------------------- |
| Pricing (per license/site)                  | 49 €      | 25 US$                                                                 | Free                                                                                    |
|                                             |           |                                                                        |                                                                                         |
| Single-step forms                           | ✅        | ✅                                                                     | ✅                                                                                      |
| Multi-step forms                            | ✅        |                                                                        |                                                                                         |
|                                             |           |                                                                        |                                                                                         |
| **Create custom forms**                     | ✅        | ✅                                                                     | ✅                                                                                      |
| ...with Blocks in the panel                 | ✅        | ✅                                                                     |                                                                                         |
| ...with Layouts (multi-column) in the panel | ✅        |                                                                        |                                                                                         |
|                                             |           |                                                                        |                                                                                         |
| **Customize behaviour after submission**    | ✅        | ✅                                                                     | ✅                                                                                      |
| ...in the panel                             | ✅        |                                                                        |                                                                                         |
| ...using hooks                              | todo      | ✅                                                                     |                                                                                         |
|                                             |           |                                                                        |                                                                                         |
| Use with JavaScript (API submission)        | ✅        | ✅                                                                     | ✅                                                                                      |
| Use without JavaScript (Form submission)    | ✅        |                                                                        | ✅                                                                                      |
|                                             |           |                                                                        |                                                                                         |
| **_Built-in fields_**                       |           |                                                                        |                                                                                         |
| Text                                        | ✅        | ✅                                                                     | <a href="#2"><sup>2</sup></a>                                                           |
| Multi-line text                             | ✅        | ✅                                                                     | <a href="#2"><sup>2</sup></a>                                                           |
| Email                                       | ✅        | ✅ _(using Input)_                                                     | <a href="#2"><sup>2</sup></a>                                                           |
| Number                                      | ✅        | ✅ _(using Input)_                                                     | <a href="#2"><sup>2</sup></a>                                                           |
| Select                                      | ✅        | ✅                                                                     | <a href="#2"><sup>2</sup></a>                                                           |
| Radio                                       | ✅        | ✅                                                                     | <a href="#2"><sup>2</sup></a>                                                           |
| Checkboxes                                  | ✅        | ✅                                                                     | <a href="#2"><sup>2</sup></a>                                                           |
| File uploads                                | todo      | ✅                                                                     | <a href="#2"><sup>2</sup></a>                                                           |
|                                             |           |                                                                        |                                                                                         |
| **_Built-in integrations_**                 |           |                                                                        |                                                                                         |
| Email                                       | ✅        | ✅                                                                     | ✅                                                                                      |
| Generic Webhook                             | ✅        |                                                                        | ✅                                                                                      |
| Gravatar                                    | ✅        |                                                                        |                                                                                         |
| Mailchimp                                   | todo      |                                                                        |                                                                                         |
| Discord                                     | todo      |                                                                        |                                                                                         |
| Slack                                       | todo      |                                                                        |                                                                                         |
| Payments (Stripe/Adyen)                     | _Roadmap_ |                                                                        |                                                                                         |
|                                             |           |                                                                        |                                                                                         |
| **_Other_**                                 |           |                                                                        |                                                                                         |
| Re-use forms in different places            | ✅        | _Each block is a unique form_                                          |                                                                                         |
| Stores submissions in the panel             | ✅        | _Sent emails will be stored_                                           |                                                                                         |
| Permissions for limiting access             | todo      |                                                                        |                                                                                         |

<small id="1"><sup>1</sup> Technically, you can built most of the features that DreamForm offers yourself using Uniform. For a fair comparison, this table only includes features that can be solved by copying code from the official docs & without writing custom code. </small>

<small id="2"><sup>2</sup> Since Uniform focuses on form validation, all fields are technically possible, but none ship with pre-built HTML snippets. </small>

## License

Kirby DreamForm is not free software. In order to run it on a public server, you'll have to purchase a valid Kirby license & a valid plugin license. Plugin licenses are tied to Kirby licenses.

Copyright 2024 © Tobias Möritz - Love & Kindness GmbH

---

The plugins' name is a homage to Kirby's Dream Land.
