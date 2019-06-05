<?php

namespace Drupal\om_code_embed\Plugin\CKEditorPlugin;

use Drupal\editor\Entity\Editor;
use Drupal\embed\EmbedCKEditorPluginBase;

/**
 * Defines the "omcode" plugin.
 *
 * @CKEditorPlugin(
 *   id = "omcode",
 *   label = @Translation("Code"),
 *   embed_type_id = "om_code"
 * )
 */
class OmCode extends EmbedCKEditorPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getFile() {
    return drupal_get_path('module', 'om_code_embed') . '/js/plugins/omcode/plugin.js';
  }

  /**
   * {@inheritdoc}
   */
  public function getConfig(Editor $editor) {
    return [
      'OmCode_dialogTitleAdd' => t('Insert Embed Code'),
      'OmCode_dialogTitleEdit' => t('Edit Embed Code'),
      'OmCode_buttons' => $this->getButtons(),
    ];
  }

}
