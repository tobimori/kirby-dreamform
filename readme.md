# Kirby Dream Form

**_Form creation, validation & handling as smooth as a Warp Star Ride._**

Kirby Dream Form is an opiniated plugin for [Kirby CMS](https://getkirby.com/) that allows you to create forms with a layout builder interface. It's easy to expand and customize, and built with Kirby's native field types.

It is currently in development and used privately for my own projects.

### Lifecycle of a Form Submission

1. The form is submitted, either via AJAX or a regular POST request
2. The `->validate()` method is called on every field
3.

## Comparison

### Dream Form vs. Uniform

[Uniform](https://kirby-uniform.readthedocs.io/en/latest/) is a toolkit for creating forms, it especially helps with server-side validation. Dream Form heavily takes inspiration and expands upon some concepts of Uniform and adds panel controls. Dream Form is not a drop-in replacement, but you can imagine it as Uniform with a panel interface.

### Dream Form vs. Form Block Suite

[Form Block Suite](https://github.com/youngcut/kirby-form-block-suite) is a great plugin with a similiar feature set to Dream Form, although with the following disadvantages over Dream Form:

- Requires the use of a proprietary AJAX script for submissions
- No custom sending behaviour, e.g. adding a CRM integration, possible
- Limited to use as Blocks, no standalone forms

## License

Kirby Dream Form is not free software. In order to run it on a public server, you'll have to purchase a valid Kirby license & a valid plugin license or subscription.

Copyright 2023 © Tobias Möritz - Love & Kindness GmbH

---

The plugins' name is a homage to Kirby's Dream Land.
