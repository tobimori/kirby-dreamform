<?php

namespace tobimori\DreamForm\Fields;

use Kirby\Cms\App;
use Kirby\Http\Remote;
use Kirby\Toolkit\Str;
use Kirby\Toolkit\V;
use tobimori\DreamForm\DreamForm;

class EmailField extends Field
{
	public static function blueprint(): array
	{
		return [
			'name' => t('dreamform.fields.email.name'),
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
			'label' => $this->block()->label()->value() ?? t('dreamform.fields.email.name'),
			'icon' => 'email',
			'type' => 'text'
		];
	}

	protected function hostname(): string
	{
		return Str::after($this->value()->value(), '@');
	}

	/**
	 * Check if the TLD associated with the email address has a valid MX record
	 */
	protected function hasMxRecord(): bool
	{
		if (DreamForm::option('fields.email.dnsLookup') === false) {
			return true;
		}

		return checkdnsrr($this->hostname(), 'MX');
	}

	/**
	 * Check if the email address is on a disposable providers black list
	 */
	protected function isDisposableEmail(): bool
	{
		if (DreamForm::option('fields.email.disposableEmails.disallow') === false) {
			return false;
		}

		$url = DreamForm::option('fields.email.disposableEmails.list');
		$list = static::cache('disposable', function () use ($url) {
			$request = Remote::get($url);
			return $request->code() === 200 ? Str::split($request->content(), PHP_EOL) : [];
		});

		return in_array($this->hostname(), $list);
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
				|| !$this->hasMxRecord()
				|| $this->isDisposableEmail())
		) {
			return $this->errorMessage();
		}

		return true;
	}
}
