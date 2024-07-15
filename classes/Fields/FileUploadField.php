<?php

namespace tobimori\DreamForm\Fields;

use Kirby\Cms\App;
use Kirby\Content\Field as ContentField;
use Kirby\Filesystem\F;
use Kirby\Http\Request\Files;
use Kirby\Toolkit\A;
use tobimori\DreamForm\DreamForm;
use tobimori\DreamForm\Models\FormPage;
use tobimori\DreamForm\Models\SubmissionPage;

class FileUploadField extends Field
{
	public static function availableTypes(): array
	{
		return DreamForm::option('fields.fileUpload.types', []);
	}

	public static function blueprint(): array
	{
		return [
			'name' => t('dreamform.fields.upload.name'),
			'preview' => 'file-upload-field',
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
							'label' => t('dreamform.fields.upload.multiple.label'),
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
							'label' => t('dreamform.fields.upload.maxSize.label'),
							'type' => 'number',
							'help' => tt('dreamform.fields.upload.maxSize.help', null, ['size' => ini_get('upload_max_filesize')]),
							'after' => 'MB',
							'width' => '1/4',
						],
						'allowedTypes' => [
							'label' => t('dreamform.fields.upload.allowedTypes.label'),
							'type' => 'multiselect',
							'width' => '3/4',
							'options' => A::map(
								array_keys(static::availableTypes()),
								fn ($type) => [
									'value' => $type,
									'text' => t("dreamform.fields.upload.allowedTypes.{$type}")
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
				!empty($types) && !A::has($types, F::mime($file['tmp_name']))
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
		$file = App::instance()->request()->files()->get($this->block()->key()->or($this->id())->value());

		if (!$file) {
			$file = [];
		}

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
			$file = $kirby->apply(
				'dreamform.upload:before',
				['file' => $file, 'field' => $this],
				'file'
			);

			$file = $submission->createFile([
				'source' => $file['tmp_name'],
				'filename' => F::safeName($file['name']),
				'template' => 'dreamform-upload',
				'content' => [
					'date' => date('Y-m-d H:i:s'),
				]
			]);

			$file = $kirby->apply(
				'dreamform.upload:after',
				['file' => $file, 'field' => $this],
				'file'
			);

			$pageFiles[] = $file;
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
	public static function isAvailable(FormPage|null $formPage = null): bool
	{
		$formPage ??= DreamForm::currentPage();

		return DreamForm::option('storeSubmissions', true) === true
			&& $formPage?->storeSubmissions()->toBool();
	}

	public function submissionBlueprint(): array|null
	{
		return [
			'label' => $this->block()->label()->value() ?? t('dreamform.fields.upload.name'),
			'type' => 'files'
		];
	}

	public static function group(): string
	{
		return 'advanced-fields';
	}
}
