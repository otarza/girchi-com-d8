<?php

/**
 * @file
 * Contains \Drupal\om_code_embed\Form\OmCodeEmbedDialog.
 */

namespace Drupal\om_code_embed\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\editor\Ajax\EditorDialogSave;
use Drupal\editor\EditorInterface;
use Drupal\embed\EmbedButtonInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form to embed code.
 */
class OmCodeEmbedDialog extends FormBase {

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * Constructs a OmCodeEmbedDialog object.
   *
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The Form Builder.
   */
  public function __construct(FormBuilderInterface $form_builder) {
    $this->formBuilder = $form_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('form_builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'om_code_embed_dialog';
  }

  /**
   * {@inheritdoc}
   *
   * @param \Drupal\editor\EditorInterface $editor
   *   The editor to which this dialog corresponds.
   * @param \Drupal\embed\EmbedButtonInterface $embed_button
   *   The URL button to which this dialog corresponds.
   */
  public function buildForm(array $form, FormStateInterface $form_state, EditorInterface $editor = NULL, EmbedButtonInterface $embed_button = NULL) {
    $values = $form_state->getValues();
    $input = $form_state->getUserInput();
    // Set URL button element in form state, so that it can be used later in
    // validateForm() function.
    $form_state->set('embed_button', $embed_button);
    $form_state->set('editor', $editor);
    // Initialize URL element with form attributes, if present.
    $om_code_element = empty($values['attributes']) ? array() : $values['attributes'];
    $om_code_element += empty($input['attributes']) ? array() : $input['attributes'];
    // The default values are set directly from \Drupal::request()->request,
    // provided by the editor plugin opening the dialog.
    if (!$form_state->get('om_code_element')) {
      $form_state->set('om_code_element', isset($input['editor_object']) ? $input['editor_object'] : array());
    }
    $om_code_element += $form_state->get('om_code_element');
    $om_code_element += array(
      'data-embed-code' => '',
      'data-embed-type' => 'default',
      'data-embed-label' => '',
    );
    $form_state->set('om_code_element', $om_code_element);

    $form['#tree'] = TRUE;
    $form['#attached']['library'][] = 'editor/drupal.editor.dialog';
    $form['#prefix'] = '<div id="om-code-embed-dialog-form">';
    $form['#suffix'] = '</div>';
    
    $form['attributes']['data-embed-label'] = array(
      '#type' => 'textfield',
      '#title' => 'Label',
      '#placeholder' => 'For keeping track in editor, not publicly visible.',
      '#default_value' => $om_code_element['data-embed-label'],
    );

    $form['attributes']['data-embed-code'] = array(
      '#type' => 'textarea',
      '#title' => 'Embed Code',
      '#rows' => 3,
      '#default_value' => $om_code_element['data-embed-code'],
      '#required' => TRUE,
    );

    $form['attributes']['data-embed-type'] = array(
      '#type' => 'select',
      '#options' => [
        'default' => 'Default',
        'square' => 'Square (1:1)',
        '16by9' => 'Video (16:9)',
      ],
      '#title' => 'Embed Type',
      '#required' => TRUE,
      '#default_value' => $om_code_element['data-embed-type'],
    );

    $form['attributes']['data-embed-button'] = array(
      '#type' => 'value',
      '#value' => $embed_button->id(),
    );
    $form['attributes']['data-entity-label'] = array(
      '#type' => 'value',
      '#value' => $embed_button->label(),
    );

    $form['actions'] = array(
      '#type' => 'actions',
    );
    $form['actions']['save_modal'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Embed'),
      '#button_type' => 'primary',
      // No regular submit-handler. This form only works via JavaScript.
      '#submit' => array(),
      '#ajax' => array(
        'callback' => '::submitForm',
        'event' => 'click',
      ),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();

    $values = $form_state->getValues();
    // Display errors in form, if any.
    if ($form_state->hasAnyErrors()) {
      unset($form['#prefix'], $form['#suffix']);
      $form['status_messages'] = array(
        '#type' => 'status_messages',
        '#weight' => -10,
      );
      $response->addCommand(new HtmlCommand('#om-code-embed-dialog-form', $form));
    }
    else {
      $response->addCommand(new EditorDialogSave($values));
      $response->addCommand(new CloseModalDialogCommand());
    }

    return $response;
  }
}
