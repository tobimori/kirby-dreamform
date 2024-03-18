<?php

use Kirby\Cms\App;

return [
	'cache.actions' => true, // Cache API calls from actions
	'multiStep' => true, // true, false
	'mode' => 'prg', // prg or api
	'debug' => fn () => App::instance()->option('debug'),
	'layouts' => [ // https://getkirby.com/docs/reference/panel/fields/layout#defining-your-own-layouts
		'1/1', '1/2, 1/2'
	],
	'page' => 'page://forms', // Slug or URI to the page where the forms are located
	'guards' => ['honeypot', 'csrf'],
	'actions' => true,
	'fields' => true,
	'integrations' => [
		'gravatar' => true, // Get profile pictures for email fields from Gravatar
		'mailchimp' => null, // mailchimp API key
		'discord' => null, // default webhook url
	]
];
