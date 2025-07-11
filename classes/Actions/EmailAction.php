<?php

namespace tobimori\DreamForm\Actions;

use Kirby\Cms\App;
use Kirby\Cms\User;
use Kirby\Filesystem\F;
use Kirby\Toolkit\A;
use tobimori\DreamForm\DreamForm;
use tobimori\DreamForm\Models\FormPage;

/**
 * Action for sending an email with the submission data.
 */
class EmailAction extends Action
{
	public const TYPE = 'email';

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
			'fields' => [
				'sendTo' => [
					'label' => t('dreamform.actions.email.sendTo.label'),
					'type' => 'dreamform-dynamic-field',
					'limitType' => 'email',
					'required' => true,
					'width' => '1/2'
				],
				'replyTo' => [
					'label' => t('dreamform.actions.email.replyTo.label'),
					'type' => 'dreamform-dynamic-field',
					'limitType' => 'email',
					'width' => '1/2'
				],
				'subject' => [
					'label' => t('dreamform.actions.email.subject.label'),
					'type' => 'text',
					'required' => true
				],
				'fieldTemplate' => [
					'label' => t('template'),
					'extends' => 'dreamform/fields/writer-with-fields',
				],
				'kirbyTemplate' => [
					'extends' => 'dreamform/fields/email-template',
					'width' => '1/2',
					'required' => true,
					'default' => 'dreamform'
				],
				'attachments' => [
					'label' => t('dreamform.actions.email.attachments.label'),
					'type' => 'multiselect',
					'options' => FormPage::getFields('file-upload'),
					'width' => '1/2',
				],
			]
		];
	}

	/**
	 * Returns the template to use for the email
	 */
	protected function template(): string|null
	{
		// legacy support for old templateType field
		if ($this->block()->templateType()->exists() && $this->block()->templateType()->isNotEmpty()) {
			$type = $this->block()->templateType()->value();

			if ($type === 'kirby') {
				return $this->block()->kirbyTemplate()->value();
			}

			if ($type === 'default' || $type === 'field') {
				return 'dreamform';
			}

			return null;
		}

		// new format: just use kirbyTemplate directly
		return $this->block()->kirbyTemplate()->value() ?: 'dreamform';
	}

	/**
	 * Returns the recipient of the email
	 */
	protected function to(): string
	{
		$value = $this->submission()->valueForDynamicField($this->block()->sendTo())->value();

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
		$value = $this->submission()->valueForDynamicField($this->block()->replyTo())?->value();

		if (!empty($value)) {
			return $value;
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
		if ($this->block()->fieldTemplate()->isNotEmpty()) {
			$html = $this->submission()->toString(
				$this->block()->fieldTemplate()->value(),
				$this->templateValues()
			);

			return $this->formatEmailBody($html);
		}

		// otherwise, let the template handle the body
		return null;
	}

	/**
	 * Formats HTML email body to plain text
	 */
	protected function formatEmailBody(string $html): array
	{
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
				'body' => $body = $this->body(),
				'data' => [
					'body' => $body,
					'action' => $this,
					'submission' => $this->submission(),
					'form' => $this->submission()->form(),
				],
				'attachments' => $this->attachments()
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
	 * Returns the attachments for the email
	 */
	protected function attachments(): array
	{
		// our file field can either store the file uuids (when already handled, aka from previous multi-step page)
		// or the PHP file object (when uploaded in the same request)
		$attachments = [];
		foreach ($this->block()->attachments()->split() as $id) {
			$value = $this->submission()->valueForId($id);

			if (is_string($value->value())) { // is a file uuid
				$files = $value->toFiles();
				foreach ($files as $file) {
					$attachments[] = $file;
				}
			} else { // is PHP file object
				$files = array_values(A::filter($value->value(), fn ($file) => $file['error'] === UPLOAD_ERR_OK));
				foreach ($files as $file) {
					$tmpName = pathinfo($file['tmp_name']);
					$filename = $tmpName['dirname'] . '/' . F::safeName($file['name']);

					if (!F::exists($filename)) {
						rename($file['tmp_name'], $filename);
					}

					$attachments[] = $filename;
				}
			}
		}

		return $attachments;
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
