<?php

namespace tobimori\DreamForm\Panel\Controller\Dialog;

use Kirby\Cms\App;
use Kirby\Cms\Find;
use Kirby\Exception\NotFoundException;
use Kirby\Panel\Controller\DialogController;
use Kirby\Panel\Ui\Dialog\FormDialog;
use Kirby\Toolkit\Str;
use tobimori\DreamForm\Models\FormPage;

/**
 * Dialog to select the form field for the form field node of writer fields
 */
class FormFieldsDialogController extends DialogController
{
	public function __construct(
		public FormPage|null $form = null,
		public string|null $field = null
	) {
		parent::__construct();
	}

	public static function factory(): static
	{
		$kirby = App::instance();
		$request = $kirby->request();
		$id = $request->get('form');

		// the writer node does not know its form, but the dialog opens from the form view
		if ($id === null && preg_match('/pages\/([^\/]+\+[^\/]+)/', $kirby->panel()->referrer(), $matches)) {
			$id = $matches[1];
		}

		try {
			$form = $id ? Find::page($id) : null;
		} catch (NotFoundException) {
			$form = null;
		}

		return new static(
			form: $form instanceof FormPage ? $form : null,
			field: $request->get('field')
		);
	}

	/**
	 * Returns the form fields that can be inserted, with key and label
	 * as JSON value for the writer node
	 */
	public function options(): array
	{
		$options = [];

		foreach ($this->form?->formFields() ?? [] as $field) {
			if (!$field::hasValue() || $field->block()->type() === 'file-upload-field') {
				continue;
			}

			$key = $field->key();
			$label = $field->label() ?: $key;

			$options[$key] = [
				'value' => json_encode(['key' => $key, 'label' => $label]),
				'text' => $label,
				'info' => Str::replace($field->block()->type(), '-field', '')
			];
		}

		return $options;
	}

	public function load(): FormDialog
	{
		$options = $this->options();
		$selected = $options[$this->field ?? ''] ?? array_values($options)[0] ?? null;

		return new FormDialog(
			fields: [
				'field' => [
					'type' => 'select',
					'label' => $this->i18n('dreamform.writerNodes.selectField'),
					'options' => array_values($options),
					'required' => true,
					'empty' => $options === [] ? $this->i18n('dreamform.common.noFields') : false
				]
			],
			submitButton: [
				'text' => $this->i18n($this->field ? 'dreamform.common.update' : 'dreamform.common.insert')
			],
			value: [
				'field' => $selected['value'] ?? null
			]
		);
	}
}
