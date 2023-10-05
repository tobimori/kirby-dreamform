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
			'tabs' => []
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
