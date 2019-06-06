<?php

namespace Drupal\girchi_my_party_list\Controller;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class PartyListController.
 */
class PartyListController extends ControllerBase {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var EntityTypeManagerInterface
   */
  protected $entityTypeManager;

    /**
     * Constructs a new PartyListController object.
     * @param EntityTypeManagerInterface $entity_type_manager
     */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

    /**
     * Party list.
     *
     * @return array Return Hello string.
     *   Return Hello string.
    */
  public function partyList() {
    return [
      '#type' => 'markup',
      '#theme' => 'girchi_my_party_list'
    ];
  }

    /**
     * Get Users.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getUsers(Request $request) {
       return new JsonResponse('It works');
    }

}
