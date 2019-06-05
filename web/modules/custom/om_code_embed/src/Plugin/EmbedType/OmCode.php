<?php

namespace Drupal\om_code_embed\Plugin\EmbedType;

use Drupal\embed\EmbedType\EmbedTypeBase;

/**
 * URL embed type.
 *
 * @EmbedType(
 *   id = "om_code",
 *   label = @Translation("Code")
 * )
 */
class OmCode extends EmbedTypeBase {

  /**
   * {@inheritdoc}
   */
  public function getDefaultIconUrl() {
    return file_create_url(drupal_get_path('module', 'om_code_embed') . '/js/plugins/omcode/omcodeembed.png');
  }

}
