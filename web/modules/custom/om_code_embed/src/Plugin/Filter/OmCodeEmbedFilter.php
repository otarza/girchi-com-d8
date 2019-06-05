<?php

namespace Drupal\om_code_embed\Plugin\Filter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\embed\DomHelperTrait;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a filter to display embedded code based on data attributes.
 *
 * @Filter(
 *   id = "om_code_embed",
 *   title = @Translation("Display embedded codes"),
 *   description = @Translation("Inserts embed codes using data attribute: data-embed-code."),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_REVERSIBLE
 * )
 */
class OmCodeEmbedFilter extends FilterBase implements ContainerFactoryPluginInterface {
  use DomHelperTrait;

  /**
   * Constructs a OmCodeEmbedFilter object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition
    );
  }

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    $result = new FilterProcessResult($text);
    if (strpos($text, 'data-embed-code') !== FALSE) {
      $dom = Html::load($text);
      $xpath = new \DOMXPath($dom);

      foreach ($xpath->query('//om-code[@data-embed-code]') as $node) {
        // Dirty way of finding out if we're processing
        // for WYSIWYG or for real output.
        if (\Drupal::routeMatch()->getRouteName() == 'embed.preview') {
          /** @var \DOMElement $node */
          $embed_label = $node->getAttribute('data-embed-label');
          $code_output = 'Embed Code';
          if ($embed_label) {
            $code_output .= ' &mdash; <strong>' . $embed_label . '</strong>';
          }
        }
        else {
          /** @var \DOMElement $node */
          $code = $node->getAttribute('data-embed-code');
          $code_output = '';
          try {
            $code_output = base64_decode($code);
          }
          catch (\Exception $e) {
          }
          if ($code_output) {
            /** @var \DOMElement $node */
            $embed_type = $node->getAttribute('data-embed-type');
            $code_output = '<div class="om-code-embed oce-type-' . $embed_type . '">' . $code_output . '</div>';
          }
        }

        $this->replaceNodeContent($node, $code_output);
      }

      $result->setProcessedText(Html::serialize($dom));
    }
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function tips($long = FALSE) {
    return $this->t('You can embed arbitrary codes.');
  }

}
