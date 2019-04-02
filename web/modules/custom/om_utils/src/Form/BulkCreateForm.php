<?php

namespace Drupal\om_utils\Form;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\system\Entity\Menu;

/**
 * Class BulkCreateForm.
 */
class BulkCreateForm extends FormBase {


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'bulk_create_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $node_types_list = \Drupal::service('entity.manager')->getStorage('node_type')->loadMultiple();
    $node_types = [];
    /**
     * @var string $machine_name
     * @var NodeType $item
     */
    foreach($node_types_list as $machine_name => $item){
      $node_types[$machine_name] = $item->label();
    }

    $langs = array_keys(\Drupal::languageManager()->getLanguages());
    $langs = implode(', ', $langs);

    $menus_list = \Drupal::service('entity.manager')->getStorage('menu')->loadMultiple();
    $menus = [];
    /**
     * @var string $machine_name
     * @var Menu $item
     */
    foreach($menus_list as $machine_name => $item){
      $menus[$machine_name] = $item->label();
    }

    $expand_options = [
      'all' => 'Expand on all levels',
      'none' => 'Don\'t expand on any level',
      '1' => 'Expand only on level 1',
    ];
    for($i = 2; $i <= 6; $i++){
      $expand_options[$i] = 'Expand on levels 1-'.$i;
    }

    $form['info'] = [
      '#type' => 'markup',
      '#markup' => '<h2>Read this first:</h2><ul>
          <li>
              See the sample CSV file <a href="https://goo.gl/bnRQ5s" target="_blank">here</a>.
          </li>
          <li>
              Put page titles for each language in separate columns. Put URL Slugs in last column.
          </li>
          <li>
              Don\'t add column headings in your CSV.
          </li>
          <li>
              Prefix sub-level with double-dashes (only for the first language column):<br>
              <code>Page on level 1<br>-- Subpage on level 2<br>-- -- Subpage on level 3</code>
          </li>
          <li>
              Language column order should match current order of languages in Drupal:<br>
              <code>'.$langs.'</code><br>
              You can set it to be reversed with checkbox below.
          </li>
        </ul>',
    ];

    $form['csv_content'] = [
      '#type' => 'textarea',
      '#title' => $this->t('CSV Content'),
      '#required' => true,
      '#description' => $this->t('Paste your CSV here.'),
    ];
    $form['content_type'] = [
      '#type' => 'select',
      '#required' => true,
      '#options' => $node_types,
      '#title' => $this->t('Content type'),
      '#description' => $this->t('Content type to create nodes for.'),
    ];
    $form['slug_field'] = [
      '#type' => 'textfield',
      '#required' => true,
      '#default_value' => 'field_url_slug',
      '#title' => $this->t('URL Slug field machine name'),
      '#description' => $this->t('Machine name of a field of selected content type that stores URL slug.'),
    ];
    if(isset($node_types['page'])){
      $form['content_type']['#default_value'] = 'page';
    }
    $form['menu'] = [
      '#type' => 'select',
      '#required' => true,
      '#options' => $menus,
      '#title' => $this->t('Menu'),
      '#description' => $this->t('Target menu to create links in.'),
    ];
    if(isset($menus['main'])){
      $form['menu']['#default_value'] = 'main';
    }
    $form['expand'] = [
      '#type' => 'select',
      '#required' => true,
      '#default_value' => 'all',
      '#options' => $expand_options,
      '#title' => $this->t('Expand menu links'),
      '#description' => $this->t('Select the number of levels to expand containing menu links.'),
    ];
    $form['lang_reverse'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Language columns in reverse order'),
      '#description' => $this->t('Check this if language columns are in reverse order, as opposed to the order described above.'),
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
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

    $csv_value = $form_state->getValue('csv_content');
    $lang_reverse = $form_state->getValue('csv_content');
    $menu = $form_state->getValue('menu');
    $expand = $form_state->getValue('expand');
    $slug_field = $form_state->getValue('slug_field');

    $pages = $this->getPagesFromCSV($csv_value, $lang_reverse);

    if(!empty($pages)){
      $this->createPages([
        'data' => $pages,
        'bundle' => $form_state->getValue('content_type'),
        'menu_name' => $menu,
        'expanded' => $expand,
        'slug_field' => $slug_field,
      ]);
    }
  }


  private function getPagesFromCSV($csv, $lang_reverse){
    $data = [];
    $csv = explode("\n", $csv);
    foreach($csv as $line) {
      $line = trim($line);
      $data[] = str_getcsv($line);
    }

    $final_data = [];
    $langs = array_keys(\Drupal::languageManager()->getLanguages());
    if($lang_reverse) {
      $langs = array_reverse($langs);
    }

    foreach($data as $line) {
      $slug_idx = count($line) - 1; // last item is slug
      if(empty($slug_idx)){
        continue; // slug is required
      }
      $temp_data = [];
      foreach($line as $idx => $val) {
        if($idx == $slug_idx) {
          $temp_data['slug'] = $val;
        } elseif(!empty($langs[$idx])) {

          if($idx === 0){
            $level = 0;
            while(substr($val, 0, 3) == '-- '){
              $level++;
              $val = Unicode::substr($val, 3);
            }
            $temp_data['level'] = $level;
          }

          $temp_data['labels'][$langs[$idx]] = $val;
        }
      }
      $final_data[] = $temp_data;
    }

    return $final_data;
  }


  private function createPages($params) {

    $data = $params['data'];
    $bundle = $params['bundle'];
    $menu_name = $params['menu_name'];
    $expanded = $params['expanded'];
    $slug_field = $params['slug_field'];

    if(is_numeric($expanded)){
      $expanded = (int)$expanded - 1;
    }

    //

    $uid = \Drupal::currentUser()->id();
    $last_links_by_levels = [];
    $level_weights = [];
    for($i = 0; $i <= 9; $i++){
      $level_weights[$i] = 0;
    }
    $prev_level = 0;

    foreach($data as $page) {
      $lang_variants = array_keys($page['labels']);
      $primary_lang = $lang_variants[0];
      unset($lang_variants[0]);

      $node = Node::create([
        'type' => $bundle,
        'langcode' => $primary_lang,
        'uid' => $uid,
        $slug_field => $page['slug'],
        'title' => $page['labels'][$primary_lang],
      ]);
      $node->save();

      $menu_link_parent = null;
      if($page['level'] > 0){
        $menu_link_parent = 'menu_link_content:' . $last_links_by_levels[$page['level'] - 1]->uuid();
      }
      if($page['level'] > $prev_level) {
        $level_weights[$page['level']] = 0;
      } else {
        $level_weights[$page['level']]++;
      }

      $menu_link_expanded = false;
      if($expanded === 'all' || $page['level'] <= $expanded){
        $menu_link_expanded = true;
      }

      $menu_link = MenuLinkContent::create([
        'title' => $page['labels'][$primary_lang],
        'link' => ['uri' => 'entity:node/' . $node->id()],
        'langcode' => $primary_lang,
        'expanded' => $menu_link_expanded,
        'menu_name' => $menu_name,
      ]);
      $menu_link->enabled->value = 1;
      $menu_link->weight->value = $level_weights[$page['level']];
      if($menu_link_parent) {
        $menu_link->parent->value = $menu_link_parent;
      }
      $menu_link->save();
      $node->save(); // to update url alias

      foreach($lang_variants as $this_lang){
        $node_translated = $node->addTranslation($this_lang);
        $node_translated->setTitle($page['labels'][$this_lang]);
        $node_translated->save();

        $menu_link_translated = $menu_link->addTranslation($this_lang, $menu_link->toArray());
        $menu_link_translated->title->value = $page['labels'][$this_lang];
        $menu_link_translated->weight->value = $level_weights[$page['level']];
        if($menu_link_parent) {
          $menu_link_translated->parent->value = $menu_link_parent;
        }
        $menu_link_translated->save();
        $node_translated->save(); // to update url alias
      }

      $last_links_by_levels[$page['level']] = $menu_link;
      $prev_level = $page['level'];

    }

  }

}
