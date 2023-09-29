<?php

@include_once __DIR__ . '/vendor/autoload.php';

use Kirby\Cms\App;
use Kirby\Data\Yaml;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;
use tobimori\DreamForm\Actions\EmailAction;
use tobimori\DreamForm\Actions\RedirectAction;
use tobimori\DreamForm\Fields\ButtonField;
use tobimori\DreamForm\Fields\CheckboxField;
use tobimori\DreamForm\Fields\EmailField;
use tobimori\DreamForm\Fields\SelectField;
use tobimori\DreamForm\Fields\TextField;

if (
	version_compare(App::version() ?? '0.0.0', '4.0.0-beta.1', '<') === true ||
	version_compare(App::version() ?? '0.0.0', '5.0.0', '>') === true
) {
	throw new Exception('Kirby Dream Form requires Kirby 4');
}

App::plugin('tobimori/dreamform', [
	'options' => [
		'actions' => [
			'email' => EmailAction::class,
			'redirect' => RedirectAction::class
		],
		'fields' => [
			'text' => TextField::class,
			'email' => EmailField::class,
			'select' => SelectField::class,
			'checkbox' => CheckboxField::class,
			'button' => ButtonField::class
		],
		'layouts' => [ // https://getkirby.com/docs/reference/panel/fields/layout#defining-your-own-layouts
			'1/1',
			'1/2, 1/2'
		]
	],
	'pageModels' => [
		'forms' => 'tobimori\DreamForm\Models\FormsPage',
		'form' => 'tobimori\DreamForm\Models\FormPage',
		'submission' => 'tobimori\DreamForm\Models\SubmissionPage',
	],
	'blueprints' => [
		'pages/forms' => __DIR__ . '/blueprints/pages/forms.yml',
		'pages/form' => __DIR__ . '/blueprints/pages/form.yml',
		'pages/submission' => __DIR__ . '/blueprints/pages/submission.yml',

		'dreamform/fields/label' => __DIR__ . '/blueprints/fields/label.yml',
		'dreamform/fields/placeholder' => __DIR__ . '/blueprints/fields/placeholder.yml',
		'dreamform/fields/error-message' => __DIR__ . '/blueprints/fields/error-message.yml',
		'dreamform/fields/required' => __DIR__ . '/blueprints/fields/required.yml',
		'dreamform/fields/actions' => require_once __DIR__ . '/blueprints/fields/actions.php',
		'dreamform/fields/fields' => require_once __DIR__ . '/blueprints/fields/fields.php',
	],
	// get all files from /translations and register them as language files
	'translations' => A::keyBy(A::map(
		Dir::read(__DIR__ . '/translations'),
		fn ($file) => A::merge([
			'lang' => F::name($file),
		], Yaml::decode(F::read(__DIR__ . '/translations/' . $file)))
	), 'lang'),
	'areas' => [
		'forms' => fn () => [
			'icon' => 'survey',
			'label' => t('forms'),
			'link' => 'pages/forms',
			'menu' => true,
			'current' => function () {
				$path = App::instance()->request()->path()->toString();
				return Str::contains($path, 'pages/forms');
			}
		]
	]
]);
