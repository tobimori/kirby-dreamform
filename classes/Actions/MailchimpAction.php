<?php

namespace tobimori\DreamForm\Actions;

use Kirby\Cms\App;
use Kirby\Data\Json;
use Kirby\Form\Form;
use Kirby\Http\Remote;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;
use tobimori\DreamForm\Models\FormPage;

class MailchimpAction extends Action
{
	public static function blueprint(): array
	{
		$lists = static::cache(
			'lists',
			fn () => static::request('GET', '/lists')?->json()
		);

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
							'width' => '2/3',
							'required' => true
						],
						'doubleOptIn' => [
							'label' => t('dreamform.double-opt-in'),
							'type' => 'toggle',
							'width' => '1/3',
							'help' => t('dreamform.double-opt-in-help')
						],
						'fieldMapping' => [
							'label' => t('dreamform.mailchimp-field-mapping'),
							'help' => t('dreamform.mailchimp-field-mapping-help'),
							'type' => 'dreamform-api-object',
							'required' => true,
							'sync' => 'list',
							'empty' => t('dreamform.mailchimp-field-mapping-empty'),
							'api' => 'mailchimp'
						],
					]
				]
			]
		];
	}

	public function run(): void
	{
	}

	public static function fieldMapping(FormPage $page, string $list): array
	{
		$fields = static::cache(
			"{$list}.fields",
			fn () => static::request('GET', "/lists/{$list}/merge-fields")?->json()
		);

		$options = FormPage::getFields();
		$blueprint = [
			'email_address' => [
				'label' => t('email'),
				'type' => 'select',
				'required' => true,
				'options' => $options,
			],
		];

		foreach ($fields['merge_fields'] as $mergeField) {
			// we don't support the following merge fields (for now) since they have additional validation
			// TODO: implement matching fields for these, and filter by them in the field mapping
			if (A::has(['address', 'birthday', 'date', 'imageurl'], $mergeField['type'])) {
				continue;
			}

			$blueprint[Str::lower($mergeField['tag'])] = [
				'label' => "{$mergeField['name']} ({$mergeField['tag']})",
				'type' => 'select',
				'options' => $options,
			];
		}

		$tags = A::reduce(static::cache(
			"{$list}.segments",
			fn () => static::request('GET', "/lists/{$list}/segments?count=1000")?->json()
		)['segments'], fn ($prev, $segment) => A::merge($prev, $segment['type'] === 'static' ? [
			"id_{$segment['id']}" => $segment['name']
		] : []), []);

		$blueprint = A::merge($blueprint, [
			'line' => [
				'type' => 'line'
			],
			'tags' => [
				'label' => t('dreamform.assign-tags-to-subscriber'),
				'type' =>  'toggles',
				'required' => true,
				'default' => 'static',
				'options' => [
					'static' => t('dreamform.static'),
					'field' => t('dreamform.from-field')
				],
				'width' => '1/3'
			],
			'tagsField' => [
				'label' => t('dreamform.tags'),
				'type' => 'select',
				'options' => $options,
				'width' => '2/3',
				'when' => [
					'tags' => 'field'
				]
			],
			'tagsStatic' => [
				'label' => t('dreamform.tags'),
				'type' => 'multiselect',
				'options' => $tags,
				'width' => '2/3',
				'when' => [
					'tags' => 'static'
				]
			]
		]);

		ray($tags);

		return (new Form([
			'fields' => $blueprint,
			'model' => $page
		]))->fields()->toArray();
	}

	/**
	 * Get the API key for the Mailchimp API
	 **/
	public static function apiKey(): string|null
	{
		$option = App::instance()->option('tobimori.dreamform.actions.mailchimp.apiKey');
		if (is_callable($option)) {
			return $option();
		}

		return $option;
	}

	public static function request(string $method, string $url, array $data = []): Remote
	{
		if ($method !== 'GET') {
			$params = [
				'data' => Json::encode($data),
				'headers' => [
					'Content-Type' => 'application/json'
				]
			];
		}

		return Remote::$method(static::apiUrl() . $url, A::merge([
			'basicAuth' => "dreamform:" . static::apiKey(),
		], $params ?? []));
	}

	public static function apiUrl(): string
	{
		$dc = Str::after(static::apiKey(), '-');
		return "https://{$dc}.api.mailchimp.com/3.0";
	}

	public static function isAvailable(): bool
	{
		if (!static::apiKey()) {
			return false;
		}

		return static::cache('ping', function () {
			$request = static::request('GET', '/ping');
			return $request->code() === 200;
		});
	}

	public static function group(): string
	{
		return 'newsletter';
	}
}
