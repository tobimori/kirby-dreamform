<?php

namespace tobimori\DreamForm\Actions;

use Kirby\Cms\App;
use Kirby\Data\Json;
use Kirby\Http\Remote;
use Kirby\Http\Url;
use Kirby\Toolkit\Str;
use tobimori\DreamForm\DreamForm;

/**
 * Action for sending an server-side event to Plausible
 */
class PlausibleAction extends Action
{
	/**
	 * Returns the Blocks fieldset blueprint for the actions' settings
	 */
	public static function blueprint(): array
	{
		return [
			'name' => t('dreamform.actions.plausible.name'),
			'preview' => 'fields',
			'wysiwyg' => true,
			'icon' => 'plausible',
			'tabs' => [
				'settings' => [
					'label' => t('dreamform.settings'),
					'fields' => [
						'event' => [
							'label' => t('dreamform.actions.plausible.event.label'),
							'type' => 'text',
							'help' => sprintf(
								'%s (link: %s text: %s target: _blank)',
								t('dreamform.actions.plausible.event.help'),
								Str::before(DreamForm::option('actions.plausible.apiUrl'), '/api') . '/' . DreamForm::option('actions.plausible.domain'),
								t('dreamform.actions.plausible.event.link')
							),
							'required' => true
						]
					]
				]
			]
		];
	}

	/**
	 * Run the action
	 */
	public function run(): void
	{
		Remote::post(DreamForm::option('actions.plausible.apiUrl') . '/event', [
			'data' => Json::encode([
				'name' => $this->block()->event()->value(),
				'domain' => DreamForm::option('actions.plausible.domain'),
				"url" => Url::toObject(App::instance()->url() . '/' . $this->submission()->referer())->toString(),
			]),
			'headers' => [
				'Content-Type' => 'application/json',
				'X-Forwarded-For' => $this->submission()->metadata()->ip()?->value(),
				'User-Agent' => $this->submission()->metadata()->userAgent()?->value(),
			]
		]);
	}

	/**
	 * Allow the action when metadata collection is enabled
	 */
	public static function isAvailable(): bool
	{
		return
			DreamForm::option('actions.plausible.domain') !== null
			&& count(array_intersect(DreamForm::option('metadata.collect'), ['ip', 'userAgent'])) === 2;
	}

	/**
	 * Returns the actions' blueprint group
	 */
	public static function group(): string
	{
		return 'analytics';
	}
}
