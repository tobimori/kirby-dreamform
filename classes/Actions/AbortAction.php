<?php

namespace tobimori\DreamForm\Actions;

/**
 * Action for aborting the submission process.
 * @package tobimori\DreamForm\Actions
 */
class AbortAction extends Action
{
  public static function blueprint(): array
  {
    return [
      'title' => t('abort-action'),
      'preview' => 'fields',
      'wysiwyg' => true,
      'icon' => 'stop',
      'tabs' => []
    ];
  }

  public function run(): void
  {
  }
}
