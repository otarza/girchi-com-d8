<?php

namespace Drupal\om_code_embed;

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
    // Return Embed::create($request, $config ?: $this->config);.
  }

}
