<?php

namespace tobimori\DreamForm\Actions;

use Kirby\Cms\App;
use Kirby\Cms\Email as CmsEmail;
use Kirby\Cms\User;
use Kirby\Email\Email;

/**
 * Action for sending an email with the submission data.
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
							'label' => t('dreamform.subject'),
							'type' => 'text',
							'required' => true
						],
						'templateType' => [
							'label' => t('dreamform.template-type'),
							'type' => 'select',
							'width' => '1/4',
							'required' => true,
							'default' => 'field',
							'options' => [
								'default' => t('dreamform.template-type-default'),
								'kirby' => t('dreamform.template-type-kirby'),
								'field' => t('dreamform.template-type-field')
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
		$type = $this->block()->templateType()->value();

		if ($type === 'kirby') {
			return $this->block()->kirbyTemplate()->value();
		}

		if ($type === 'default') {
			return 'dreamform';
		}

		return null;
	}

	public function to(): string
	{
		if ($this->block()->sendTo()->value() === 'field') {
			return $this->submission()->valueForId($this->block()->sendToField())->value();
		}

		return $this->block()->sendToStatic()->value();
	}

	public function replyTo(): string
	{
		if ($this->block()->replyTo()->value() === 'field') {
			return $this->submission()->valueForId($this->block()->replyToField())->value();
		}

		if (($static = $this->block()->replyToStatic())->isNotEmpty()) {
			return $static->value();
		}

		return $this::from()->email();
	}


	public function body(): array|null
	{
		if ($this->block()->templateType()->value() !== 'field') {
			return null;
		}

		$html = $this->submission()->toString(
			$this->block()->fieldTemplate()->value(),
			$this->submission()->values()->toArray()
		);

		return [
			'html' => $html,
			'text' => strip_tags($html)
		];
	}

	public function subject()
	{
		return $this->submission()->toString(
			$this->block()->subject()->value(),
			$this->submission()->values()->toArray()
		);
	}

	public static function from(): User
	{
		$name = App::instance()->option('tobimori.dreamform.actions.email.from.name');
		if (is_callable($name)) {
			$name = $name();
		}

		$email = App::instance()->option('tobimori.dreamform.actions.email.from.email');
		if (is_callable($email)) {
			$email = $email();
		}

		return new User(compact('name', 'email'));
	}

	public function run(): void
	{
		try {
			App::instance()->email([
				'template' => $this->template(),
				'from' => $this::from(),
				'replyTo' => $this->replyTo(),
				'to' => $this->to(),
				'subject' => $this->subject(),
				'body' => $this->body(),
				'data' => [
					'action' => $this,
					'submission' => $this->submission(),
					'form' => $this->submission()->form(),
				],
			]);
		} catch (\Exception $e) {
			$this->cancel($e->getMessage());
		}
	}
}
