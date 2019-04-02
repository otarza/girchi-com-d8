<?php

namespace Drupal\om_content_authoring\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\image\Plugin\Field\FieldFormatter\ImageFormatter;

/**
 * Plugin implementation of the 'image' formatter.
 *
 * @FieldFormatter(
 *   id = "image_ignore_gif",
 *   label = @Translation("Image (Exclude GIF Processing)"),
 *   field_types = {
 *     "image"
 *   }
 * )
 */
class IgnoreGifImageFormatter extends ImageFormatter {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);

    // If configured to use an image style, remove the image style from the
    // render array data for any images that are GIFs.
    $image_style = $this->getSetting('image_style');
    if (!empty($image_style)) {
      foreach ($elements as $delta => $element) {
        $mimetype = $element['#item']->entity->filemime->value;
        if ($mimetype == 'image/gif') {
          $elements[$delta]['#image_style'] = '';
        }
      }
    }

    return $elements;
  }

}
