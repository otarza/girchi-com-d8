<?php

namespace Drupal\om_site_settings\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ConfigManager;
use Drupal\Core\Config\ExtensionInstallStorage;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManager;
use FtpClient\FtpException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SiteSettingsForm.
 *
 * @package Drupal\om_site_settings\Form
 */
class SiteSettingsForm extends ConfigFormBase {

  /**
   * Drupal\Core\Config\ConfigManager definition.
   *
   * @var \Drupal\Core\Config\ConfigManager
   */
  protected $configManager;

  /**
   * Drupal\Core\Config\ExtensionInstallStorage definition.
   *
   * @var \Drupal\Core\Config\ExtensionInstallStorage
   */
  protected $configStorageSchema;

  /**
   * Drupal\Core\Language\LanguageManager definition.
   *
   * @var \Drupal\Core\Language\LanguageManager
   */
  protected $languageManager;

  /**
   * Constructs a new GeneralSettingsForm object.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    ConfigManager $config_manager,
    ExtensionInstallStorage $config_storage_schema,
    LanguageManager $language_manager
  ) {
    parent::__construct($config_factory);
    $this->configManager = $config_manager;
    $this->configStorageSchema = $config_storage_schema;
    $this->languageManager = $language_manager;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('config.manager'),
      $container->get('config.storage.schema'),
      $container->get('language_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'om_site_settings.site_settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'om_site_settings_site_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('om_site_settings.site_settings');
    $schema = $this->configStorageSchema->read('om_site_settings.schema');

    $types = [
        'string' => 'textfield',
        'label' => 'textfield',
        'text' => 'textarea',
        'full_html' => 'text_format'
    ];

    if (!empty($schema['om_site_settings.site_settings']['mapping'])) {
      foreach ($schema['om_site_settings.site_settings']['mapping'] as $field_key => $field) {
        if ((isset($field['skip_auto_form']) && $field['skip_auto_form']) || !isset($types[$field['type']])) {
          continue;
        }


        $default_value =  $types[$field['type']] == 'text_format' ?  $config->get($field_key)['value'] : $config->get($field_key);


        $form[$field_key] = [
          '#type' => $types[$field['type']],
          '#title' => $field['label'],
          '#default_value' =>  $default_value,
        ];
      }
    }

    $form['accreditation_email_text'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Accreditation Approved Email Text'),
      '#default_value' => $config->get('accreditation_email_text'),
      '#description' => 'Placeholders:<br>
        <code>@requested_on</code> - Date request was submitted<br>
        <code>@requested_for</code> - Organisation name<br>
        <code>@approved_people</code> - List of people request was made for',
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('om_site_settings.site_settings');
    parent::submitForm($form, $form_state);

    $schema = $this->configStorageSchema->read('om_site_settings.schema');

    if (!empty($schema['om_site_settings.site_settings']['mapping'])) {
      foreach ($schema['om_site_settings.site_settings']['mapping'] as $field_key => $field) {
        if (isset($field['skip_auto_form']) && $field['skip_auto_form']) {
          continue;
        }

        $this->config('om_site_settings.site_settings')
          ->set($field_key, $form_state->getValue($field_key))
          ->save();
      }
    }

    $this->config('om_site_settings.site_settings')
      ->set('accreditation_email_text', $form_state->getValue('accreditation_email_text'))
      ->save();

  }

  public function RefreshFTP(array &$form, FormStateInterface $form_state) {
    /* TODO: Add refresh functionality after add xml parsers and etc. */
  }


}
