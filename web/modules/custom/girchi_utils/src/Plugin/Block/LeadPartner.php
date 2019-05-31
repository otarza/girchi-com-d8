<?php

namespace Drupal\girchi_utils\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\taxonomy\Entity\Term;

/**
 * Provides a 'LeadPartner' block.
 *
 * @Block(
 *  id = "lead_partner",
 *  admin_label = @Translation("Lead partner"),
 * )
 */
class LeadPartner extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
      $query = \Drupal::entityQuery('taxonomy_term')
          ->condition('vid', 'lead_partner')
          ->condition('status', 1)
          ->sort('field_weight',"DESC")
          ->range(0,10);

      $tids = $query->execute();
      $top_partners = Term::loadMultiple($tids);
      $final_partners = [];
      foreach ($top_partners as $partner){
          $name = $partner->getName();
          $weight = $partner->get('field_weight')->value;
          $donation = $partner->get('field_donated_amount')->value;
          $img = $partner->get('field_image')->get(0)->entity->getFileUri();
          $final_partners[] = ['name' => $name, "weight" => $weight, 'donation' => $donation, 'img' => $img ];
      }

      return array(
          '#theme' => 'lead_partners',
          '#leadPartner' => $final_partners,
      );
  }

}
