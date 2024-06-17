<?php

use Kirby\Cms\App;
use Kirby\Toolkit\A;
use tobimori\DreamForm\DreamForm;

return function () {
	return [
		'title' => 'dreamform.form',
		'image' => [
			'icon' => 'survey',
			'query' => 'icon',
			'back' => '#fafafa'
		],
		'num' => 0,
		'options' => [
			'preview' => false,
			'move' => false
		],
		'create' => [
			'title' => [
				'label' => 'dreamform.form.formName.label'
			]
		],
		'status' => [
			'draft' => [
				'label' => 'dreamform.form.status.draft.label',
				'text' => 'dreamform.form.status.draft.text'
			],
			'unlisted' => false,
			'listed' => [
				'label' => 'dreamform.form.status.listed.label',
				'text' => 'dreamform.form.status.listed.text'
			]
		],
		'tabs' => [
			'fields' => [
				'label' => 'dreamform.fields',
				'icon' => 'input-cursor-move',
				'fields' => [
					'fields' => 'dreamform/fields/fields'
				]
			],
			'workflow' => [
				'label' => 'dreamform.workflow',
				'icon' => 'folder-structure',
				'fields' => [
					'actions' => 'dreamform/fields/actions'
				]
			],
			'submissions' => App::instance()->user()?->role()->permissions()->for('tobimori.dreamform', 'accessSubmissions') ? 'dreamform/tabs/form-submissions' : false,
			'settings' => [
				'label' => 'dreamform.settings',
				'icon' => 'cog',
				'columns' => A::merge(
					[
						[
							'width' => '1/4',
							'fields' => [
								'_success' => [
									'label' => 'dreamform.form.successPage.label',
									'type' => 'headline'
								]
							]
						],
						[
							'width' => '3/4',
							'fields' => [
								'success' => [
									'type' => 'group',
									'extends' => 'dreamform/fields/success'
								]
							]
						],
						[
							'width' => '1',
							'fields' => [
								'_line' => [
									'type' => 'line'
								]
							]
						],
						[
							'width' => '1/4',
							'fields' => [
								'_workflow' => [
									'label' => 'dreamform.workflow',
									'type' => 'headline'
								]
							]
						],
						[
							'width' => '3/4',
							'fields' => [
								'continueOnError' => [
									'label' => 'dreamform.form.continueOnError.label',
									'type' => 'toggle',
									'help' => 'dreamform.form.continueOnError.help',
									'width' => '1/3'
								]
							]
						],
					],
					DreamForm::option('storeSubmissions') ? [
						[
							'width' => '1',
							'fields' => [
								'_line2' => [
									'type' => 'line'
								]
							]
						],
						[
							'width' => '1/4',
							'fields' => [
								'_submissions' => [
									'label' => 'dreamform.submissions',
									'type' => 'headline'
								]
							]
						],
						[
							'width' => '3/4',
							'fields' => [
								'storeSubmissions' => [
									'label' => 'dreamform.form.storeSubmissions.label',
									'type' => 'toggle',
									'default' => true,
									'help' => 'dreamform.form.storeSubmissions.help',
									'width' => '1/3'
								]
							]
						],
					] : []
				)
			]
		]
	];
};
