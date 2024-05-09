<?php

use Kirby\Cms\App;

return [
	'cache' => [
		'sessionless' => [
			'active' => true,
		],
		'fields' => [
			'active' => true
		],
		'performer' => [
			'active' => true
		]
	],
	'useDataAttributes' => false, // uses data-form-url instead of action
	'mode' => 'prg', // prg / api / htmx
	'multiStep' => true, // Enable multi-step forms
	'storeSubmissions' => true, // Store submissions in the content folder
	'debug' => fn () => App::instance()->option('debug'),
	'layouts' => [ // https://getkirby.com/docs/reference/panel/fields/layout#defining-your-own-layouts
		'1/1', '1/2, 1/2'
	],
	'page' => 'page://forms', // Slug or URI to the page where the forms are located
	'secret' => null, // Encryption secret for htmx attributes
	'metadata' => [
		'collect' => [] // 'ip' | 'userAgent'
	],
	'guards' => [
		'available' => ['honeypot', 'csrf'],
		'honeypot' => [
			'fields' => ['website', 'email', 'name', 'url', 'birthdate', 'comment', 'summary', 'subject']
		],
		'akismet' => [
			'apiKey' => null,
			'fields' => [
				'comment_author' => ['name', 'first-name', 'last-name', 'username'],
				'comment_author_email' => ['email', 'mail', 'e-mail', 'email-address', 'emailaddress'],
				'comment_author_url' => ['website', 'url', 'homepage', 'website-url'],
				'comment_content' => ['message', 'comment', 'content', 'body', 'text', 'description']
			]
		],
		'turnstile' => [
			'theme' => 'auto',
			'siteKey' => null,
			'secretKey' => null,
			'injectScript' => true
		],
		'ratelimit' => [
			'limit' => 10,
			'interval' => 3
		]
	],
	'fields' => [
		'available' => true,
		'email' => [
			'dnsLookup' => true,
			'disposableEmails' => [
				'disallow' => true,
				'list' => 'https://raw.githubusercontent.com/disposable/disposable-email-domains/master/domains.txt'
			]
		],
		'pages' => [
			'query' => 'site.childrenAndDrafts.filterBy("intendedTemplate", "!=", "forms")' // Page query for the pages field type
		],
		'fileUpload' => [
			'types' => [
				// JPEG, PNG, GIF, AVIF, WEBP
				'images' => ["image/jpeg", "image/png", "image/gif", "image/avif", "image/webp",],
				// MP3, OGG, OPUS, WAV, WEBM
				'audio' => ["audio/mpeg", "audio/ogg", "audio/opus", "audio/aac", "audio/wav", "audio/webm"],
				// AVI, MP4, MPEG, OGG, WEBM
				'video' => ["video/x-msvideo", "video/mp4", "video/mpeg", "video/ogg", "video/webm"],
				// PDF, DOC, XLS, PPT
				'documents' => ["application/pdf", "application/msword", "application/vnd.ms-excel", "application/vnd.ms-powerpoint"],
				// ZIP, RAR, TAR, 7Z
				'archives' => ["application/zip", "application/x-rar-compressed", "application/x-tar", "application/x-7z-compressed"]
			]
		]
	],
	'actions' => [
		'available' => true,
		'discord' => [
			'webhook' => null // Default webhook URL
		],
		'mailchimp' => [
			'apiKey' => null // Mailchimp API key
		],
		'buttondown' => [
			'apiKey' => null, // Buttondown API key
			'simpleMode' => false // Simple mode supports free plans, removes tags support
		],
		'email' => [
			'from' => [
				'email' => fn () => App::instance()->option('email.transport.username'),
				'name' => fn () => App::instance()->site()->title()
			]
		],
		'plausible' => [
			'domain' => 'piqy.de',
			'apiUrl' => 'https://plausible.moeritz.io/api'
		]
	],
	'integrations' => [
		'gravatar' => true, // Get profile pictures for email fields from Gravatar
	]
];
