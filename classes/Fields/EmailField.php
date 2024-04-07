<?php

namespace tobimori\DreamForm\Fields;

use Kirby\Cms\App;
use Kirby\Toolkit\Str;
use Kirby\Toolkit\V;

class EmailField extends Field
{
	public static function blueprint(): array
	{
		return [
			'title' => t('dreamform.email-field'),
			'preview' => 'text-field',
			'wysiwyg' => true,
			'icon' => 'email',
			'tabs' => [
				'field' => [
					'label' => t('dreamform.field'),
					'fields' => [
						'key' => 'dreamform/fields/key',
						'label' => 'dreamform/fields/label',
						'placeholder' => 'dreamform/fields/placeholder',
					]
				],
				'validation' => [
					'label' => t('dreamform.validation'),
					'fields' => [
						'required' => 'dreamform/fields/required',
						'errorMessage' => 'dreamform/fields/error-message',
					]
				]
			]
		];
	}

	public function submissionBlueprint(): array|null
	{
		return [
			'label' => $this->block()->label()->value() ?? t('dreamform.email-field'),
			'icon' => 'email',
			'type' => 'text'
		];
	}

	/**
	 * Check if the TLD associated with the email address has a valid MX record
	 */
	protected function hasMxRecord(): bool
	{
		if (App::instance()->option('tobimori.dreamform.fields.email.dnsLookup') === false) {
			return true;
		}

		$hostname = Str::after($this->value()->value(), '@');
		return checkdnsrr($hostname, 'MX');
	}

	/**
	 * Validate the email field
	 */
	public function validate(): true|string
	{
		if (
			$this->block()->required()->toBool()
			&& $this->value()->isEmpty()
			|| $this->value()->isNotEmpty()
			&& (!V::email($this->value()->value())
				|| !$this->hasMxRecord())
		) {
			return $this->errorMessage();
		}

		return true;
	}
}
