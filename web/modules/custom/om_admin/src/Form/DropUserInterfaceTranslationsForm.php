<?php

namespace Drupal\om_admin\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Language\Language;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\language\ConfigurableLanguageManager;
use Drupal\Core\Database\Driver\mysql\Connection;

/**
 * Class DropUserInterfaceTranslationsForm.
 */
class DropUserInterfaceTranslationsForm extends ConfigFormBase
{

  /**
   * Drupal\language\ConfigurableLanguageManager definition.
   *
   * @var \Drupal\language\ConfigurableLanguageManager
   */
  protected $languageManager;
  /**
   * Drupal\Core\Database\Driver\mysql\Connection definition.
   *
   * @var \Drupal\Core\Database\Driver\mysql\Connection
   */
  protected $database;

  /**
   * Constructs a new DropUserInterfaceTranslationsForm object.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    ConfigurableLanguageManager $language_manager,
    Connection $database
  )
  {
    parent::__construct($config_factory);
    $this->languageManager = $language_manager;
    $this->database = $database;
  }

  public static function create(ContainerInterface $container)
  {
    return new static(
      $container->get('config.factory'),
      $container->get('language_manager'),
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames()
  {
    return [
      'om_admin.dropuserinterfacetranslations',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'drop_user_interface_translations_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $languages = $this->languageManager->getLanguages();
    $options = ['' => $this->t('- None -')];
    /** @var Language $language */
    foreach($languages as $id => $language) {
      if($id != 'en') {
        $options[$id] = $language->getName();
      }
    }

    $form['language_to_drop'] = [
      '#type' => 'select',
      '#title' => $this->t('Select language to drop its User Interface Translations'),
      '#description' => $this->t('All User Interface string translations for this language will be deleted!'),
      '#default_value' => '',
      '#options' => $options,
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state)
  {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $to_drop = $form_state->getValue('language_to_drop');
    if($to_drop) {
      if($language = $this->languageManager->getLanguage($to_drop)) {
        $delete = $this->database->delete('locales_target');
        $delete->condition('language', $to_drop);
        $deleted_num = $delete->execute();
        drupal_flush_all_caches();

        drupal_set_message($deleted_num.' items deleted from '.$language->getName().' interface translations.', 'status');
      } else {
        drupal_set_message('There is no language <code>'.$to_drop.'</code>', 'error');
      }
    } else {
      drupal_set_message('Please select language to drop translations for.', 'error');
    }
  }

}
