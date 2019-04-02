<?php

/**
 * @file
 * Contains Drupal\om_code_embed\OmCodeEmbed.
 */

namespace Drupal\om_code_embed;

use Embed\Embed;

/**
 * A service class for handling Code embeds.
 */
class OmCodeEmbed {

  /**
   * @var array
   */
  public $config;

  /**
   * @{inheritdoc}
   */
  public function __construct(array $config = []) {
    $this->config = $config;
  }

  /**
   * @{inheritdoc}
   */
  public function getConfig() {
    return $this->config;
  }

  /**
   * @{inheritdoc}
   */
  public function setConfig(array $config) {
    $this->config = $config;
  }

  /**
   * @{inheritdoc}
   */
  public function getEmbed($request, array $config = []) {
    return 'embeeeed';
    //return Embed::create($request, $config ?: $this->config);
  }

}
