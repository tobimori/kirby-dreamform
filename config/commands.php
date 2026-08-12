<?php

use Kirby\Data\Json;
use tobimori\DreamForm\DreamForm;

return [
	'dreamform:migrate' => [
		'description' => 'Migrates forms from old email action syntax to new syntax',
		'args' => [
			'dry-run' => [
				'prefix' => 'd',
				'longPrefix' => 'dry-run',
				'description' => 'Preview changes without saving',
				'noValue' => true
			]
		],
		'command' => function ($cli) {
			$dryRun = $cli->arg('dry-run');
			$kirby = kirby();
			$kirby->impersonate('kirby');

			$formsPage = DreamForm::findPageOrDraftRecursive(DreamForm::option('page'));
			if (!$formsPage) {
				$cli->error('Forms page not found');
				return;
			}

			$forms = $formsPage->index()->filterBy('intendedTemplate', 'form');
			$total = $forms->count();

			if ($total === 0) {
				$cli->out('No forms found');
				return;
			}

			$languages = $kirby->languages();
			$langCodes = $languages->count() > 0
				? $languages->pluck('code')
				: [null];

			$cli->out($dryRun ? "Checking {$total} forms..." : "Migrating {$total} forms...");
			$cli->br();

			$progress = $cli->progress()->total($total);
			$migrated = 0;
			$skipped = 0;
			$errors = [];

			foreach ($forms as $form) {
				$formId = $form->id();
				$formTitle = $form->title()->value() ?? $form->slug();
				$formMigrated = false;

				$cli->inline("\r\033[K{$formTitle}");

				foreach ($langCodes as $langCode) {
					try {
						$contentFile = $langCode
							? $form->root() . '/form.' . $langCode . '.txt'
							: $form->root() . '/form.txt';

						if (!file_exists($contentFile)) {
							continue;
						}

						$actions = Json::decode(
							$form->content($langCode)->get('actions')->value() ?: '[]'
						);

						$changed = false;
						$c = null;

						foreach ($actions as &$action) {
							if (($action['type'] ?? null) !== 'email-action') {
								continue;
							}

							$c = &$action['content'];

							if (isset($c['sendto']) && is_array($c['sendto'])) {
								continue;
							}

							$changed = true;

							if (isset($c['sendto']) && is_string($c['sendto'])) {
								$c['sendto'] = $c['sendto'] === 'field'
									? ['type' => 'dynamic', 'field' => $c['sendtofield'] ?? null]
									: ['type' => 'static', 'value' => $c['sendtostatic'] ?? null];
								unset($c['sendtofield'], $c['sendtostatic']);
							}

							if (isset($c['replyto']) && is_string($c['replyto'])) {
								$c['replyto'] = $c['replyto'] === 'field'
									? ['type' => 'dynamic', 'field' => $c['replytofield'] ?? null]
									: ['type' => 'static', 'value' => $c['replytostatic'] ?? null];
								unset($c['replytofield'], $c['replytostatic']);
							}

							unset($c['templatetype']);

							if (empty($c['kirbytemplate'])) {
								$c['kirbytemplate'] = 'dreamform';
							}
						}
						unset($c, $action);

						if ($changed && !$dryRun) {
							$kirby->page($formId)->update(
								['actions' => Json::encode($actions)],
								$langCode
							);
						}

						$formMigrated = $formMigrated || $changed;
					} catch (\Throwable $e) {
						$errors[] = "{$formTitle} ({$langCode}): {$e->getMessage()}";
					}
				}

				$formMigrated ? $migrated++ : $skipped++;
				$progress->advance();
			}

			$cli->inline("\r\033[K");
			$cli->br();

			if ($dryRun) {
				$cli->bold()->out("{$migrated} forms need migration, {$skipped} up to date");
				if ($migrated > 0) {
					$cli->br();
					$cli->dim()->out('Run without --dry-run to apply changes');
				}
			} else {
				$cli->bold()->out("{$migrated} migrated, {$skipped} skipped");
			}

			if (count($errors) > 0) {
				$cli->br();
				$cli->error('Errors:');
				foreach ($errors as $error) {
					$cli->dim()->out("  {$error}");
				}
			}
		}
	]
];
