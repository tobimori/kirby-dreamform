---
title: Demo & Example
description: Learn from the open-source example integration
---

You can find the open-source example integration of DreamForm on [GitHub](https://github.com/tobimori/dreamform-plainkit). This example uses [HTMX](https://htmx.org/) for progressively enhancing form submissions and [Tailwind CSS](https://tailwindcss.com/) for styling the form.

To keep things simple, we're using the CDN versions of both tools. If you're deploying a site with Tailwind in production, make sure to add a build step for Tailwind CSS.

## Get up and running

Download or clone the repository from GitHub and switch into the created directory.

```bash
git clone https://github.com/tobimori/dreamform-plainkit df-plainkit && cd df-plainkit
```

Install Kirby 4, DreamForm and other utility plugins (like [DotEnv](https://getkirby.com/plugins/bnomei/dotenv) for environment variables & [Ray](https://getkirby.com/plugins/genxbe/ray) for debugging) using [Composer](https://getcomposer.org/). If you don't have Composer yet, follow the installation guide on the Composer website.

```bash
composer install
```

Copy the `.env.example` file to `.env` and add a randomly generated secret that is used for encrypting the snippet parameters for use with HTMX.

After that, you're ready to go: start the PHP dev server with composer as well. You'll now be able to open the DreamForm site in your browser under `http://localhost:8000`.

```bash
composer start
```

## The Demo Explained

### `/site/templates/default.php`

[This template](https://github.com/tobimori/dreamform-plainkit/blob/main/site/templates/default.php) is rendered when opening the page in your browser. It contains a call to the `layout` snippet which renders the HTML shell and some general markup. The important part is the call to the `dreamform/form` snippet. In here, all attached classes are specified and the form is rendered.

```php
<?php
    /* Render the form selected in the page */
    snippet('dreamform/form', [
      'form' => $page->form()->toPage(),
      'attr' => [
        'row' => ['class' => 'gap-4 mb-4'],
        'field' => ['class' => 'group flex flex-col items-start mb-4 last:mb-0'],
        'label' => ['class' => 'text-sm font-medium text-slate-600 mb-1'],
        'input' => ['class' => 'peer group-data-[has-error]:placeholder-shown:mb-2 shadow-sm group-data-[has-error]:placeholder-shown:border-red-500 w-full border border-gray-200 rounded p-2'],
        'button' => ['class' => 'ml-auto bg-indigo-600 text-white py-2 px-4 rounded shadow-sm hover:bg-indigo-800 transition-colors duration-200'],
        'error' => ['class' => 'peer-[:not(:placeholder-shown)]:hidden text-red-500 text-sm'],
        'radio' => [
          'label' => ['class' => 'text-sm font-medium text-slate-600 mb-2'],
          'input' => ['class' => 'w-4 rounded-full mr-2 shadow-sm border border-gray-200 '],
          'error' => ['class' => 'group-has-[:checked]:hidden text-red-500 text-sm'],
          'row' => ['class' => 'flex items-center mb-1 text-sm'],
        ],
        'checkbox' =>  [
          'label' => ['class' => 'text-sm font-medium text-slate-600 mb-2'],
          'input' => ['class' => 'w-4 mr-2 shadow-sm border border-gray-200 rounded'],
          'error' => ['class' => 'group-has-[:checked]:hidden text-red-500 text-sm'],
          'row' => ['class' => 'flex items-center mb-1 text-sm'],
        ],
        'file' => [
          'input' => ['class' => 'mt-1 w-full border border-gray-200 group-data-[has-error]:mb-2 group-data-[has-error]:border-red-500 text-gray-600 shadow-sm rounded file:cursor-pointer file:transition-colors file:bg-gray-100 file:border-0 file:mr-3 file:py-2 file:px-4 file:hover:bg-gray-200'],
          'row' => ['class' => 'flex items-center mb-1 text-sm'],
        ]
      ]
    ]); ?>
```

---

### `/site/blueprints/pages/default.yml`

The corresponding Page blueprint for that template [is located here](https://github.com/tobimori/dreamform-plainkit/blob/main/site/blueprints/pages/default.yml). It uses the `dreamform/fields/form` panel field that can be used to select a form. The stored value is then rendered by the template above.

```yaml
title: Default Page

columns:
  main:
    width: 2/3
    sections:
      fields:
        type: fields
        fields:
          text:
            toolbar:
              inline: false
            type: writer
            inline: true
          form: dreamform/fields/form
  sidebar:
    width: 1/3
    sections:
      pages:
        type: pages
        template: default
      files:
        type: files
```

---

### `/site/blueprints/users/editor.yml`

[This file is the user blueprint for the editor role.](https://github.com/tobimori/dreamform-plainkit/blob/main/site/blueprints/users/editor.yml) We want our editors to not be able to create or update forms, but they should be able to view submissions and forms. This is done by setting the DreamForm permissions here. By default, all users have access to all permissions. You can [read more about Permissions here.](https://plugins.andkindness.com/dreamform/docs/developers/permissions)

```yaml
title: Editor
permissions:
  tobimori.dreamform:
    "*": false
    accessForms: true
    accessSubmissions: true
```

---

### `/site/snippets/layout.php`

[This is a basic layout snippet](https://github.com/tobimori/dreamform-plainkit/blob/main/site/snippets/layout.php) that renders the site scaffolding. It's used as [Kirby snippet with slots](https://getkirby.com/docs/guide/templates/snippets#passing-slots-to-snippets).

---

### `/site/snippets/dreamform/success.php`

[This is a custom success snippet](https://github.com/tobimori/dreamform-plainkit/blob/main/site/snippets/dreamform/success.php) shown after a successful form submission. You can generally override all DreamForm snippets by putting a equally named part in your `/site/snippets` folder.

---

### `/site/snippets/dreamform/fields/linkedin.php`

[This is the field snippet](https://github.com/tobimori/dreamform-plainkit/blob/main/site/snippets/dreamform/fields/linkedin.php) for the custom LinkedIn field we defined in `/site/plugins/dreamform-demo/LinkedInField.php`. To not copy any code from the plugin, we're simply rendering the built-in DreamForm text field snippet here, since the LinkedIn field just adds custom validation rules.

---

### `/site/plugins/dreamform-demo/LinkedInField.php`

[This is a custom field](https://github.com/tobimori/dreamform-plainkit/blob/main/site/plugins/dreamform-demo/LinkedInField.php) that validates LinkedIn Profile URLs, used in our example Job application form. The HTML snippet can be found above. You can [read more about building your own custom fields here.](https://plugins.andkindness.com/dreamform/docs/fields/custom)

---

### `/site/plugins/dreamform-demo/index.php`

[This file](https://github.com/tobimori/dreamform-plainkit/blob/main/site/plugins/dreamform-demo/index.php) registers our custom LinkedIn field with DreamForm as well as registers the plugin with Kirby.

---

### `/site/config/config.php`

[This is our Kirby config file.](https://github.com/tobimori/dreamform-plainkit/blob/main/site/config/config.php) It sets some important defaults for using DreamForm, such as our HTMX mode or removing the [CSRF guard](https://plugins.andkindness.com/dreamform/docs/guards/csrf) so we can be accept form submissions on cached websites.
