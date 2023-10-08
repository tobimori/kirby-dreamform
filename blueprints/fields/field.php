<?php

use tobimori\DreamForm\Models\FormPage;

return function () {
  $fields = FormPage::getFields(kirby()->request());

  return [
    'label' => t('field'),
    'type' => 'select',
    'options' => $fields,
  ];
};
