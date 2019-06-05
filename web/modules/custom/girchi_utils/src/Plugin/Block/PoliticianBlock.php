<?php

namespace Drupal\girchi_utils\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'PoliticianBlock' block.
 *
 * @Block(
 *  id = "politician_block",
 *  admin_label = @Translation("Politician block"),
 * )
 */
class PoliticianBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $build['politician_block']['#markup'] = 'Implement PoliticianBlock.';
    $language = \Drupal::languageManager()->getCurrentLanguage()->getId();

    return [
      '#theme' => 'politician_block',
      '#language' => $language,
    ];
  }

  /**
   *
   */
  public function getCacheMaxAge() {
    // Set block cache max age 3 hours and then invalidate.
    return 10800;
  }

}
