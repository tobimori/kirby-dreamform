<?php

namespace tobimori\DreamForm\Actions;

use Kirby\Cms\User;

/**
 * Action for sending an email with the submission data.
 * @package tobimori\DreamForm\Actions
 */
class EmailAction extends Action
{
	public static function blueprint(): array
	{
		return [
			'title' => t('send-email-action'),
			'preview' => 'fields',
			'wysiwyg' => true,
			'icon' => 'email',
			'tabs' => [
				'addresses' => [
					'label' => t('addresses'),
					'fields' => [
						'sendTo' => [
							'label' => t('send-to'),
							'extends' => 'dreamform/fields/static-dynamic-toggles',
						],
						'sendToField' => [
							'label' => ' ',
							'extends' => 'dreamform/fields/field',
							'width' => '3/4',
							'when' => [
								'sendTo' => 'field'
							]
						],
						'sendToStatic' => [
							'label' => ' ',
							'type' => 'text',
							'width' => '3/4',
							'placeholder' => 'tobimori@dreamform.com',
							'when' => [
								'sendTo' => 'static'
							]
						],
						'replyTo' => [
							'label' => t('reply-to'),
							'extends' => 'dreamform/fields/static-dynamic-toggles',
						],
						'replyToField' => [
							'label' => ' ',
							'extends' => 'dreamform/fields/field',
							'width' => '3/4',
							'when' => [
								'replyTo' => 'field'
							]
						],
						'replyToStatic' => [
							'label' => ' ',
							'type' => 'text',
							'width' => '3/4',
							'when' => [
								'replyTo' => 'static'
							]
						]
					]
				],
				'template' => [
					'label' => t('template'),
					'fields' => [
						'subject' => [
							'label' => t('subject'),
							'type' => 'text',
						],
						'templateType' => [
							'label' => t('template-type'),
							'type' => 'select',
							'width' => '1/4',
							'required' => true,
							'options' => [
								'default' => t('template-type-default'),
								'kirby' => t('template-type-kirby'),
								'field' => t('template-type-field')
							],
						],
						'kirbyTemplate' => [
							'extends' => 'dreamform/fields/email-template',
							'width' => '3/4',
							'when' => [
								'templateType' => 'kirby'
							]
						],
						'fieldTemplate' => [
							'label' => t('template'),
							'extends' => 'dreamform/fields/writer-with-fields',
							'width' => '3/4',
							'when' => [
								'templateType' => 'field'
							]
						]
					]
				]
			]
		];
	}

	public function run(): void
	{
		$kirby = kirby();

		// works for now
		$kirby->email([
			'template' => 'form',
			'from' => new User([
				'name' => $kirby->site()->name(),
				'email' => $kirby->option('email.transport.username')
			]),
			'replyTo' => $this->submission()->fields()->findBy('id', '957a729e-6f0e-425e-b4cd-43edfbfd838c')->value()->value(),
			'to' => $this->submission()->fields()->findBy('id', '89c4d3cd-59dc-41ac-93fe-ff3b8657fa9f')->value()->value(),
			'subject' => 'Kontaktformular',
			'data' => [
				'fields' => $this->submission()->fields()
			]
		]);
	}
}
