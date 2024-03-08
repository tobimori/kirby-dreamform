<?php

namespace tobimori\DreamForm\Actions;

use Kirby\Cms\User;

/**
 * Action for sending an email with the submission data.
 *
 * @package tobimori\DreamForm\Actions
 */
class EmailAction extends Action
{
	public static function blueprint(): array
	{
		return [
			'title' => t('dreamform.send-email-action'),
			'preview' => 'fields',
			'wysiwyg' => true,
			'icon' => 'email',
			'tabs' => [
				'addresses' => [
					'label' => t('dreamform.addresses'),
					'fields' => [
						'sendTo' => [
							'label' => t('dreamform.send-to'),
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
							'placeholder' => t('email.placeholder'),
							'when' => [
								'sendTo' => 'static'
							]
						],
						'replyTo' => [
							'label' => t('dreamform.reply-to'),
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
							'required' => true
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

	public function template(): string|null
	{
		$type = $this->action()->templateType()->value();

		if ($type === 'kirby') {
			return $this->action()->kirbyTemplate()->value();
		}

		if ($type === 'default') {
			return 'dreamform';
		}

		return null;
	}

	public function to(): string
	{
		if ($this->action()->sendTo()->value() === 'field') {
			return $this->submission()->fields()->findBy('id', $this->action()->sendToField()->value())->value()->value();
		}

		return $this->action()->sendToStatic()->value();
	}

	public function replyTo(): string
	{
		if ($this->action()->replyTo()->value() === 'field') {
			return $this->submission()->fields()->findBy('id', $this->action()->replyToField()->value())->value()->value();
		}

		if (($static = $this->action()->replyToStatic())->isNotEmpty()) {
			return $static->value();
		}

		return kirby()->option('email.transport.username');
	}


	public function body(): array|null
	{
		if ($this->action()->templateType()->value() !== 'field') {
			return null;
		}

		$html = ($this->submission()->referer() ?? $this->submission())->toSafeString(
			$this->action()->fieldTemplate()->value(),
			$this->submission()->fieldValues()
		);

		return [
			'html' => $html,
			'text' => strip_tags($html)
		];
	}

	public function subject()
	{
		return ($this->submission()->referer() ?? $this->submission())->toSafeString(
			$this->action()->subject()->value(),
			$this->submission()->fieldValues()
		);
	}

	public function run(): void
	{
		kirby()->email([
			'template' => $this->template(),
			'from' => new User([
				'name' => site()->title(),
				'email' => option('tobimori.dreamform.email', option('email.transport.username'))
			]),
			'replyTo' => $this->replyTo(),
			'to' => $this->to(),
			'subject' => $this->subject(),
			'body' => $this->body(),
			'data' => [
				'action' => $this,
				'submission' => $this->submission(),
				'fields' => $this->submission()->fields(),
			],
		]);
	}
}
