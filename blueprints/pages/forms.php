<?php

use Kirby\Cms\App;

return function () {
	return [
		'title' => 'dreamform.forms',
		'image' => [
			'icon' => 'survey',
			'query' => 'icon',
			'back' => '#fafafa'
		],
		'options' => [
			'create' => false,
			'preview' => false,
			'delete' => false,
			'changeSlug' => false,
			'changeStatus' => false,
			'duplicate' => false,
			'changeTitle' => false,
			'update' => false
		],
		'status' => [
			'draft' => false,
			'unlisted' => true,
			'listed' => false
		],
		'tabs' => [
			'forms' => [
				'label' => 'dreamform.forms',
				'icon' => 'survey',
				'sections' => [
					'license' => [
						'type' => 'dreamform-license'
					],
					'forms' => [
						'label' => 'dreamform.forms',
						'type' => 'pages',
						'empty' => 'dreamform.forms.empty',
						'template' => 'form',
						'image' => false
					]
				]
			],
			'submissions' => App::instance()->user()?->role()->permissions()->for('tobimori.dreamform', 'accessSubmissions') ? [
				'label' => 'dreamform.submissions.recent',
				'icon' => 'archive',
				'sections' => [
					'license' => [
						'type' => 'dreamform-license'
					],
					'submissions' => [
						'label' => 'dreamform.submissions.recent',
						'type' => 'pages',
						'empty' => 'dreamform.submissions.empty',
						'template' => 'submission',
						'layout' => 'table',
						'create' => false,
						'text' => false,
						// TODO: cache the query as it seems to be slow on larger sites (> 1 sec)
						'query' => "page.index.filterBy('intendedTemplate', 'submission').sortBy('sortDate', 'desc').limit(20)",
						'columns' => [
							'date' => [
								'label' => 'dreamform.submission.submittedAt',
								'type' => 'html',
								'value' => '<a href="{{ page.panel.url }}">{{ page.title }}</a>',
								'mobile' => true
							],
							'form' => [
								'label' => 'dreamform.form',
								'type' => 'html',
								'value' => '<a href="{{ page.parent.panel.url }}?tab=submissions">{{ page.parent.title }}</a>'
							]
						]
					]
				]
			] : false,
		]
	];
};
