<?php

namespace Drupal\om_content_authoring\Plugin\Filter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\embed\DomHelperTrait;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a filter to display embedded URLs based on data attributes.
 *
 * @Filter(
 *   id = "om_text_transform",
 *   title = @Translation("Apply Omedia Text Transformations"),
 *   description = @Translation("Wraps URL Embeds, cleans text, does (will do) some other stuff. Move as top as possible, after cleaning and limiting allowed HTML tags."),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_REVERSIBLE
 * )
 */
class TextTransformFilter extends FilterBase implements ContainerFactoryPluginInterface {
  use DomHelperTrait;

  /**
   * Constructs a TextTransformFilter object.
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

    if (strpos($text, 'data-embed-url') !== FALSE || strpos($text, '<img') !== FALSE) {
      $dom = Html::load($text);
      $xpath = new \DOMXPath($dom);

      // Wrap URL embeds in div.url-embed-wrapper.
      if (strpos($text, 'data-embed-url') !== FALSE) {
        $wrapper = $dom->createElement('div');
        $wrapper->setAttribute('class', 'url-embed-wrapper');

        foreach ($xpath->query('//drupal-url[@data-embed-url]') as $node) {
          /** @var \DOMElement $node */
          $new_wrapper = $wrapper->cloneNode();
          $node->parentNode->replaceChild($new_wrapper, $node);
          $new_wrapper->appendChild($node);
        }
      }

      // Wrap aligned but non-captioned images into the same figure as captioned ones have.
      // Captioned ones have <figure class="align-... inline-image"> wrappers.
      // inline-image class is added in this .module file.
      if (strpos($text, '<img') !== FALSE) {
        $wrapper = $dom->createElement('figure');

        foreach ($xpath->query("//img[contains(concat(' ', normalize-space(@class)), ' align-')][not(ancestor::figure)]") as $node) {
          /** @var \DOMElement $node */
          $new_wrapper = $wrapper->cloneNode();
          $classes = $node->getAttribute('class');
          $node->removeAttribute('class');
          $new_wrapper->setAttribute('class', $classes . ' inline-image');

          if ($node->parentNode->tagName == 'a') {
            $link_node = $node->parentNode;
            $link_node->parentNode->replaceChild($new_wrapper, $link_node);
            $new_wrapper->appendChild($link_node);
          }
          else {
            $node->parentNode->replaceChild($new_wrapper, $node);
            $new_wrapper->appendChild($node);
          }
        }
      }
      /*
       * THIS SHIT DOES WORK BUT BREAKS WYSIWYG WHEN CAPTIONED. DAIKIDE.
      // Captioned media elements are wrapped in <figure> by drupal's filter_caption
      // We need to: 1. replace wrapping <figure> with <div>
      //             2. move filter_caption's <figcaption> inside real <figure> (from media--image.html.twig)
      //             3. move only text from filter_caption's <figcaption> if real <figure> already has a
      //                figcaption with source field's <em> in it.
      // Good luck figuring this code out :)
      $wrapper = $dom->createElement('div');
      foreach($xpath->query("//figure[contains(concat(' ', normalize-space(@class), ' '), ' embedded-entity ')]") as $node) {
      $new_wrapper = $wrapper->cloneNode();
      $new_wrapper->setAttribute('class', $node->getAttribute('class'));
      if($node->hasChildNodes()) {
      while($node->childNodes->length > 0) {
      $new_wrapper->appendChild($node->childNodes->item(0));
      }
      }
      foreach($new_wrapper->getElementsByTagName('figcaption') as $figcaption) {
      if($figcaption->parentNode->nodeName != 'figure') {
      $figure = $new_wrapper->getElementsByTagName('figure');
      if ($figure->length > 0) {
      $existing_figcaption = $figure->item(0)->getElementsByTagName('figcaption');
      if($existing_figcaption->length > 0) {
      $existing_figcaption->item(0)->insertBefore($figcaption->childNodes->item(0), $existing_figcaption->item(0)->getElementsByTagName('em')->item(0));
      $figcaption->parentNode->removeChild($figcaption);
      } else {
      $figure->item(0)->appendChild($figcaption);
      }
      }
      }
      }
      $node->parentNode->replaceChild($new_wrapper, $node);
      }
       */

      $html = Html::serialize($dom);
      $result->setProcessedText($html);
    }
    return $result;
  }

}
