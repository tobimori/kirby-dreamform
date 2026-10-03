---
title: Custom Actions
description: Creating custom actions for DreamForm
---

Extending DreamForm by creating custom actions is easy. In this guide, we're creating a custom action for sending messages to Discord using their webhook feature. If you don't know what an action is yet, check out ["What are actions?"](what-are-actions.md).

## When to choose an action

* You want to send submitted data somewhere after submission
* You want to cancel a submission if certain conditions are met
* It should be customizable when your action runs
* You need panel controls for configuring the action
* You don't need to add something to the template, your code runs entirely in the back

If any of those is a deal-breaker for you, you might be better off with a [field](../2_fields/12_custom.md) or [guard](../4_guards/0_what-are-guards.md).

## Creating the plugin

In DreamForm, every action is a class. In theory, you don't have to create a Kirby plugin as long as the action class is loaded correctly. But since Kirby autoloads plugins and they can also be used to share fields between projects, we're using a plugin here.

Create a new folder in `site/plugins` called `discord-action` and add an `index.php` file. In this file, define your plugin as you would normally do:

```php
// site/plugins/discord-action/index.php

use Kirby\Cms\App as Kirby;

Kirby::plugin('tobimori/discord-action', []);
```

## The action class

Let's create a new file for our class, `DiscordAction.php`. A basic action class only needs the `blueprint()`, `type()` and `run()` methods, but the following are available as well:

* `public static function blueprint(): array`   
  Returns an array with the blueprint definition used in the panel
* `public static function type(): string`  
  Returns the type of the field used internally, also used for accessing the snippet
* `public static function group(): string`   
  Customize the group of the action in the panel
* `public function run(): void`   
  The logic that's executed when the action is running
* `public static function isAvailable(FormPage|null $form = null): bool`  
  Whether the action should be available, used for checking global configuration details, like API keys

Okay, that was a lot, let's get back to the basics. We can start with the following, defining the `blueprint()` and the `type()` of the action.

```php
<?php

use tobimori\DreamForm\Actions\Action;

class DiscordAction extends Action
{
  public static function blueprint(): array
  {
    return [
      'title' => 'Discord Message',
      'preview' => 'fields',
      'wysiwyg' => true,
      'icon' => 'discord',
      'fields' => [
        'webhookUrl' => [
          'label' => 'Webhook URL',
          'type' => 'url',
          'placeholder' => 'https://discord.com/api/webhooks/...',
          'width' => '1/3',
          'required' => true
        ]
      ]
    ];
  }

  public function run(): void
  {
    // send the request
  }

  public static function type(): string
  {
    return 'discord';
  }
}
```

## Registering the action

Add the class to the `tobimori.dreamform.actions` key in your plugin's `index.php`. Use Kirby's `load()` function or Composer to load the class lazily. This lets Kirby load your plugin before DreamForm, and lets the plugin register without DreamForm installed.

```php
// [...]

load([
  'DiscordAction' => __DIR__ . '/DiscordAction.php',
]);

Kirby::plugin('tobimori/discord-action', [
  'tobimori.dreamform.actions' => [
    DiscordAction::class,
  ],
]);
```

The list uses each class's `type()`. To register a custom type or replace an existing type, use an associative entry, such as `'custom-action' => DiscordAction::class`. Built-in types load first, then plugin declarations in Kirby's plugin order. The last declaration for a type wins.

`DreamForm::register()` and the type-specific registration methods still work in v2. They take priority over plugin declarations. These methods are deprecated and will be removed in v3; move registrations to the plugin key before upgrading.

DreamForm automatically uses the type returned by `type()`. You should now be able to see the action available in your panel.

## Sending the webhook request

Discord has an API endpoint for sending messages. Using the Kirby Remote class, we're going to send a POST request to that endpoint.

```php
<?php

class DiscordAction extends Action
{
  [...]

  public function run(): void
  {
    try {
      $request = Remote::post($this->block()->webhookUrl()->value(), [
        'headers' => [
          'Content-Type' => 'application/json'
        ],
        'data' => Json::encode([
          'content' => A::join(A::map($this->submission()->values()->toArray(), function ($value, $key) {
            return "**{$key}**: {$value}";
          }), "\n"),
          'embeds' => null,
          'attachments' => []
        ])
      ]);

      if ($request->code() > 299) {
        $this->cancel();
      }
    } catch (Throwable $e) {
      $this->cancel($e->getMessage());
    }
  }
}
```

To turn the submitted values in a human-readable string, we're using `$this->submission()->values()` and mapping it to a line with the key and answer included.

In this code snippet, you might notice the method calls to `$this->cancel()` in case the request errors. In general, there are a bunch of methods available that you can call inside your action, namely:

* `$this->submission()`  
  Access the submission
* `$this->form()`  
  Access the form
* `$this->block()`  
  Access the block used to configure the action
* `$this->success()`  
  End the submission early, and show the success screen
* `$this->cancel(string $message = null, bool $public = false)`  
  Cancel the request with an error message - If $public is false, the error message is only shown when debug mode is enabled
* `$this->silentCancel(string $message = null)`  
  Silently cancel the form submission - Submission will be shown as successful to the user, except if debug mode is enabled

This way, you can easily build out actions the way you want. Let's add our action to the form and test it. If you need more inspiration, take a look at the source code of the built-in actions of DreamForm.