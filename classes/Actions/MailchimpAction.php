<?php

namespace tobimori\DreamForm\Actions;

use Kirby\Data\Json;
use Kirby\Http\Remote;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;

/**
 * @package tobimori\DreamForm\Actions
 */
class MailchimpAction extends Action
{
	public static function blueprint(): array
	{
		$lists = static::cache(
			'mailchimp.lists',
			fn () => static::request('GET', '/lists')?->json()
		);

		// kirby requires us to get all field mappings for all lists upfront
		// this is a bit of a problem because we don't know which list the user will select
		// TODO: find a way to only fetch the fields for the selected list (custom panel field)
		/* $fields = A::map($lists['lists'], function ($list) {
			return static::cache(
				"mailchimp.{$list['id']}.fields",
				fn () => static::request('GET', "/lists/{$list['id']}/merge-fields")?->json()
			);
		}); */

		return [
			'title' => t('dreamform.mailchimp-action'),
			'preview' => 'fields',
			'wysiwyg' => true,
			'icon' => 'mailchimp',
			'tabs' => [
				'settings' => [
					'label' => t('dreamform.settings'),
					'fields' => [
						'list' => [
							'label' => t('dreamform.mailchimp-list'),
							'type' => 'select',
							'options' => A::reduce($lists['lists'], fn ($prev, $list) => A::merge($prev, [
								$list['id'] => $list['name']
							]), []),
							'width' => '1/3',
							'required' => true
						],
						'fieldMapping' => [
							'label' => t('dreamform.mailchimp-field-mapping'),
							'help' => t('dreamform.mailchimp-field-mapping-help'),
							'type' => 'object',
							'width' => '2/3',
						],
						'doubleOptIn' => [
							'label' => t('dreamform.double-opt-in'),
							'type' => 'toggle',
							'width' => '1/3',
							'help' => t('dreamform.double-opt-in-help')
						],
					]
				]
			]
		];
	}

	public function run(): void
	{
	}

	public static function request(string $method, string $url, array $data = []): Remote
	{
		$apiToken = option('tobimori.dreamform.integrations.mailchimp');
		if ($method !== 'GET') {
			$params = [
				'data' => Json::encode($data),
				'headers' => [
					'Content-Type' => 'application/json'
				]
			];
		}

		return Remote::$method(static::apiUrl() . $url, A::merge([
			'basicAuth' => "dreamform:{$apiToken}",
		], $params ?? []));
	}

	public static function apiUrl(): string
	{
		$apiToken = option('tobimori.dreamform.integrations.mailchimp');
		$dc = Str::after($apiToken, '-');
		return "https://{$dc}.api.mailchimp.com/3.0";
	}

	public static function isAvailable(): bool
	{
		$apiToken = option('tobimori.dreamform.integrations.mailchimp');
		if (!$apiToken) {
			return false;
		}

		return static::cache('mailchimp.ping', function () {
			$request = static::request('GET', '/ping');
			return $request->code() === 200;
		});
	}

	public static function group(): string
	{
		return 'email';
	}
}
