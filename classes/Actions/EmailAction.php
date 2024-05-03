<?php

namespace tobimori\DreamForm\Actions;

use Exception;
use Kirby\Cms\App;
use Kirby\Cms\User;
use Kirby\Parsley\Parsley;
use Kirby\Toolkit\A;

/**
 * Action for sending an email with the submission data.
 */
class EmailAction extends Action
{
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

	public function body(): array|null
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

	public function subject()
	{
		return $this->submission()->toString(
			$this->block()->subject()->value(),
			$this->templateValues()
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

		if (empty($name) || empty($email)) {
			throw new Exception('[DreamForm] No sender email or transport username specified in the config.');
		}

		return new User(compact('name', 'email'));
	}

	public function run(): void
	{
		try {
			$email = App::instance()->email([
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
}
