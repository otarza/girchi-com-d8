<?php

function omedia_form_system_theme_settings_alter(&$form, \Drupal\Core\Form\FormStateInterface &$form_state, $form_id = NULL) {

  // Work-around for a core bug affecting admin themes. See issue #943212.
  if (isset($form_id)) {
    return;
  }

  $form['omedia_svg_icon_file'] = array(
    '#type'          => 'textfield',
    '#title'         => t('SVG icon file'),
    '#default_value' => theme_get_setting('omedia_svg_icon_file'),
    '#description'   => t('Location of SVG icon sprite file used by <code>{{ svg_icon() }}</code> Twig function and <code>omedia.svg_icon()</code> JavaScript function.<br>Relative to theme folder. Default: <code>images/icons.svg</code>'),
  );

  unset($form['theme_settings']);
  unset($form['logo']);
  unset($form['favicon']);

  /*

  more on configuration module:
  https://www.drupal.org/docs/8/creating-custom-modules/defining-and-using-your-own-configuration-in-drupal-8
  https://www.drupal.org/docs/8/api/configuration-api/configuration-schemametadata

  */

}
