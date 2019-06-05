<?php

namespace Drupal\om_tag_widget\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\Crypt;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\EntityReferenceAutocompleteTagsWidget;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\Url;

/**
 * A widget bar.
 *
 * @FieldWidget(
 *   id = "entity_reference_om_tag_widget",
 *   label = @Translation("Om Autocomplete (Tags style)"),
 *   description = @Translation("An Omedia autocomplete text field with tagging
 *   support."),
 *   field_types = {
 *     "entity_reference"
 *   },
 *   multiple_values = TRUE
 * )
 */
class OmTagWidget extends EntityReferenceAutocompleteTagsWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(
    FieldItemListInterface $items,
    $delta,
    array $element,
    array &$form,
    FormStateInterface $form_state
  ) {
    $element = parent::formElement($items, $delta, $element, $form,
      $form_state);

    // Create autocomplete url.
    $target_type = $this->getFieldSetting('target_type');
    $selection_handler = $this->getFieldSetting('handler');
    $selection_settings = $element['target_id']['#selection_settings'];

    // Serialize data to crypt it with site hash salt.
    $serialize_data = serialize($selection_settings) . $target_type . $selection_handler;
    $selection_settings_key = Crypt::hmacBase64($serialize_data,
      Settings::getHashSalt());

    // Get KeyValueStorage and check if this key exists, if not exists add it.
    $key_value_storage = \Drupal::keyValue('entity_autocomplete');
    if (!$key_value_storage->has($selection_settings_key)) {
      $key_value_storage->set($selection_settings_key, $selection_settings);
    }

    // Generate autocomplete url from this settings.
    $url = Url::fromRoute('system.entity_autocomplete',
      [
        'target_type' => $target_type,
        'selection_handler' => $selection_handler,
        'selection_settings_key' => $selection_settings_key,
      ]
    )->toString();

    $element += [
      '#attached' => [
        'library' => [
          'om_tag_widget/tagging-js',
        ],
      ],
    ];

    return $element;
  }

}
