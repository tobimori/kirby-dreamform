<?php

namespace tobimori\DreamForm\Fields;

use Kirby\Cms\App;
use Kirby\Cms\File;
use Kirby\Content\Field as ContentField;
use Kirby\Filesystem\F;
use tobimori\DreamForm\Models\SubmissionPage;

class FileUploadField extends Field
{
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
						'label' => 'dreamform/fields/label',
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
						'required' => 'dreamform/fields/required',
						'errorMessage' => 'dreamform/fields/error-message',
					]
				]
			]
		];
	}

	// abusing the sanitize method to get the file from the request
	protected function sanitize(ContentField $value): ContentField
	{
		$file = App::instance()->request()->files()->get($this->key());

		return new ContentField($value->parent(), $this->key(), $file);
	}

	public function afterSubmit(SubmissionPage $submission): void
	{

		/** @var array $file */
		$file = $this->value()->value();

		ray($file);

		($kirby = App::instance())->impersonate('kirby');
		File::create([
			'source' => $file['tmp_name'],
			'parent' => $submission,
			'filename' => F::safeName($file['name']),
			'content' => [
				'date' => date('Y-m-d H:i:s'),
			]
		]);
		$kirby->impersonate();
	}

	public function submissionBlueprint(): array|null
	{
		return [
			'label' => t('dreamform.file-upload-field') . ': ' . $this->key(),
			'icon' => 'hidden',
			'type' => 'text'
		];
	}

	public static function group(): string
	{
		return 'advanced-fields';
	}
}
