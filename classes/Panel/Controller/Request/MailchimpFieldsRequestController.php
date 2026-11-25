<?php

namespace tobimori\DreamForm\Panel\Controller\Request;

use Kirby\Cms\Find;
use Kirby\Exception\NotFoundException;
use Kirby\Panel\Controller\RequestController;
use tobimori\DreamForm\Actions\MailchimpAction;
use tobimori\DreamForm\Models\FormPage;

/**
 * Returns the field mapping of the Mailchimp action for an audience
 */
class MailchimpFieldsRequestController extends RequestController
{
	public function __construct(
		public FormPage $form,
		public string $list
	) {
		parent::__construct();
	}

	/**
	 * @throws NotFoundException If the page is not a form or the user cannot access it
	 */
	public static function factory(string $id, string $list): static
	{
		$form = Find::page($id);

		if (!($form instanceof FormPage)) {
			throw new NotFoundException(
				key: 'page.notFound',
				data: ['slug' => $id]
			);
		}

		return new static(form: $form, list: $list);
	}

	public function load(): array
	{
		return MailchimpAction::fieldMapping($this->form, $this->list);
	}
}
