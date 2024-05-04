<?php

namespace tobimori\DreamForm\Actions;

use Kirby\Cms\App;
use Kirby\Cms\User;
use Kirby\Toolkit\A;
use tobimori\DreamForm\DreamForm;

/**
 * Action for sending an email with the submission data.
 */
class EmailAction extends Action
{
	/**
	 * Returns the Blocks fieldset blueprint for the actions' settings
	 */
	public static function blueprint(): array
	{
		return [
			'name' => t('dreamform.actions.email.name'),
			'preview' => 'fields',
			'wysiwyg' => true,
			'icon' => 'email',
			'tabs' => [
				'addresses' => [
					'label' => t('dreamform.actions.email.addresses.label'),
					'fields' => [
						'sendTo' => [
							'label' => t('dreamform.actions.email.sendTo.label'),
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
							'label' => t('dreamform.actions.email.replyTo.label'),
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
							'label' => t('dreamform.actions.email.subject.label'),
							'type' => 'text',
							'required' => true
						],
						'templateType' => [
							'label' => t('dreamform.actions.email.templateType.label'),
							'type' => 'select',
							'width' => '1/4',
							'required' => true,
							'default' => 'default',
							'options' => [
								'default' => t('dreamform.actions.email.templateType.default'),
								'kirby' => t('dreamform.actions.email.templateType.kirby'),
								'field' => t('dreamform.actions.email.templateType.field')
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

	/**
	 * Returns the template to use for the email
	 */
	protected function template(): string|null
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

	/**
	 * Returns the recipient of the email
	 */
	protected function to(): string
	{
		if ($this->block()->sendTo()->value() === 'field') {
			$value = $this->submission()->valueForId($this->block()->sendToField())->value();
		} else {
			$value = $this->block()->sendToStatic()->value();
		}

		if (empty($value)) {
			$this->silentCancel('dreamform.actions.email.error.recipient');
		}

		return $value;
	}

	/**
	 * Returns the reply-to address of the email
	 */
	protected function replyTo(): string
	{
		if ($this->block()->replyTo()->value() === 'field') {
			return $this->submission()->valueForId($this->block()->replyToField())->value();
		}

		if (($static = $this->block()->replyToStatic())->isNotEmpty()) {
			return $static->value();
		}

		return $this->from()->email();
	}

	/**
	 * Returns the values for the query email template
	 */
	protected function templateValues(): array
	{
		return A::merge(
			$this->submission()->values()->toArray(),
			[
				'page' => $this->submission()->findRefererPage(),
				'submission' => $this->submission(),
				'form' => $this->submission()->form(),
			]
		);
	}

	/**
	 * Returns the body of the email
	 */
	protected function body(): array|null
	{
		if ($this->block()->templateType()->value() !== 'field') {
			return null;
		}

		$html = $this->submission()->toString(
			$this->block()->fieldTemplate()->value(),
			$this->templateValues()
		);

		return [
			'html' => $html,

			// i wish we had a pipe operator
			'text' => html_entity_decode(
				trim(
					strip_tags(
						preg_replace(
							'/<h1>|<h2>|<h3>|<h4>|<h5>|<h6>|<p>|<div>|<br>|<ul>|<ol>|<li>/',
							"\n",
							$html
						)
					)
				)
			)
		];
	}

	/**
	 * Returns the subject of the email
	 */
	public function subject()
	{
		return $this->submission()->toString(
			$this->block()->subject()->value(),
			$this->templateValues()
		);
	}

	/**
	 * Returns the sender of the email
	 */
	public function from(): User
	{
		$name = DreamForm::option('actions.email.from.name');
		$email = DreamForm::option('actions.email.from.email');

		if (empty($name) || empty($email)) {
			$this->cancel('dreamform.actions.email.error.sender');
		}

		return new User(compact('name', 'email'));
	}

	/**
	 * Run the action
	 */
	public function run(): void
	{
		try {
			$email = App::instance()->email([
				'template' => $this->template(),
				'from' => $this->from(),
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

			$this->log([
				'template' => [
					'to' => array_keys($email->to())[0],
				],
				'from' => $email->from(),
				'subject' => $email->subject(),
				'body' => $email->body()->text()
			], type: 'email', icon: 'email', title: 'dreamform.actions.email.log.success');
		} catch (\Exception $e) {
			$this->cancel($e->getMessage());
		}
	}

	/**
	 * Returns the base log settings for the action
	 */
	protected function logSettings(): array|bool
	{
		return [
			'icon' => 'email',
			'title' => 'dreamform.actions.email.name'
		];
	}
}
