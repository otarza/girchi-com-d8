<?php

namespace Drupal\girchi_utils\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormBuilder;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityFormBuilderInterface;

/**
 * Class SupportersRegistrationController.
 */
class SupportersRegistrationController extends ControllerBase {

  /**
   * Drupal\Core\Entity\EntityFormBuilderInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityFormBuilderInterface
   */
  protected $entityFormBuilder;

  protected $formBuilder;

  /**
   * Constructs a new SupportersRegistrationController object.
   *
   * @param EntityFormBuilderInterface $entity_form_builder
   * @param FormBuilder $formBuilder
   */
  public function __construct(EntityFormBuilderInterface $entity_form_builder,
                              FormBuilder $formBuilder) {
    $this->entityFormBuilder = $entity_form_builder;
    $this->formBuilder = $formBuilder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.form_builder'),
      $container->get('form_builder')
    );
  }

  /**
   * Registerpage.
   *
   * @return array|string
   *   Return Hello string.
   */
  public function registerPage() {

    $form = $this->entityFormBuilder->getForm(User::create([]),
      'register');

    return [
      '#theme' => 'supporter_registration',
      '#form' => $form
    ];
  }

}
