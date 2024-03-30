<?php

namespace tobimori\DreamForm\Fields;

use Kirby\Cms\App;
use Kirby\Content\Field as ContentField;
use Kirby\Filesystem\F;
use Kirby\Toolkit\A;
use tobimori\DreamForm\DreamForm;
use tobimori\DreamForm\Models\SubmissionPage;

class FileUploadField extends Field
{
	public static function availableTypes(): array
	{
		return App::instance()->option('tobimori.dreamform.fields.fileUpload.types', []);
	}

	public static function blueprint(): array
	{
		return [
			'title' => t('dreamform.file-upload-field'),
			'label' => '{{ key }}',
			'preview' => 'fields',
			'wysiwyg' => true,
			'icon' => 'upload',
			'tabs' => [
				'field' => [
					'label' => t('dreamform.field'),
					'fields' => [
						'key' => 'dreamform/fields/key',
						'label' => [
							'extends' => 'dreamform/fields/label',
							'width' => '1/2',
						],
						'allowMultiple' => [
							'label' => t('dreamform.allow-multiple'),
							'type' => 'toggle',
							'default' => false,
							'width' => '1/3',
						],
					]
				],
				'validation' => [
					'label' => t('dreamform.validation'),
					'fields' => [
						'maxSize' => [
							'label' => t('dreamform.max-filesize'),
							'type' => 'number',
							'help' => tt('dreamform.max-filesize-ini', null, ['size' => ini_get('upload_max_filesize')]),
							'after' => 'MB',
							'width' => '1/4',
						],
						'allowedTypes' => [
							'label' => t('dreamform.limit-file-types'),
							'type' => 'multiselect',
							'width' => '3/4',
							'options' => A::map(
								array_keys(static::availableTypes()),
								fn ($type) => [
									'value' => $type,
									'text' => t("dreamform.filetype-{$type}")
								]
							)
						],
						'required' => 'dreamform/fields/required',
						'errorMessage' => 'dreamform/fields/error-message',
					]
				]
			]
		];
	}

	public function validate(): true|string
	{
		$files = array_values(A::filter($this->value()->value(), fn ($file) => $file['error'] === UPLOAD_ERR_OK));

		if ($this->block()->required()->toBool() && empty($files)) {
			return $this->errorMessage();
		}

		if (empty($files)) {
			return true;
		}

		$types = [];
		foreach ($this->block()->allowedTypes()->split() as $type) {
			if (isset(static::availableTypes()[$type])) {
				$types = A::merge($types, static::availableTypes()[$type]);
			}
		}

		foreach ($files as $file) {
			if (
				!A::has($types, F::mime($file['tmp_name']))
				|| $file['size'] > ($this->block()->maxSize()->isNotEmpty() ? $this->block()->maxSize()->toInt() * 1024 * 1024 : INF)
			) {
				return $this->errorMessage();
			}
		}

		return true;
	}

	// abusing the sanitize method to get the file from the request
	protected function sanitize(ContentField $value): ContentField
	{
		$file = App::instance()->request()->files()->get($this->key());

		if (!array_is_list($file)) {
			$file = [$file];
		}

		return new ContentField($value->parent(), $this->key(), $file);
	}

	/**
	 * Store the file in the submission
	 */
	public function afterSubmit(SubmissionPage $submission): void
	{
		/** @var array $file */
		$files = array_values(A::filter($this->value()->value(), fn ($file) => $file['error'] === UPLOAD_ERR_OK));

		if (empty($files)) {
			return;
		}

		$pageFiles = [];
		($kirby = App::instance())->impersonate('kirby');
		foreach ($files as $file) {
			$pageFiles[] = $submission->createFile([
				'source' => $file['tmp_name'],
				'filename' => F::safeName($file['name']),
				'template' => 'dreamform-upload',
				'content' => [
					'date' => date('Y-m-d H:i:s'),
				]
			]);
		}
		$kirby->impersonate();

		$this->value = new ContentField(
			$submission,
			$this->key(),
			A::join(A::map($pageFiles, fn ($file) => "- {$file->uuid()->toString()}\n"), '')
		);
		$submission->setField($this)->saveSubmission();
	}

	/**
	 * Get the file from the submission
	 */
	public static function isAvailable(): bool
	{
		return
			App::instance()->option('tobimori.dreamform.storeSubmissions', true) === true
			&& DreamForm::currentPage()?->storeSubmissions()->toBool();
	}

	public function submissionBlueprint(): array|null
	{
		return [
			'label' => $this->block()->label()->value() ?? t('dreamform.file-upload-field'),
			'type' => 'files'
		];
	}

	public static function group(): string
	{
		return 'advanced-fields';
	}
}
