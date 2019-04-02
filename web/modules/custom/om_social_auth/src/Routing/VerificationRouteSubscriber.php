<?php

namespace Drupal\om_social_auth\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class VerificationRouteSubscriber.
 *
 * @package Drupal\om_social_auth\Routing
 * Listens to the dynamic route events.
 */
class VerificationRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    if (!empty($collection->get('user_registrationpassword.confirm'))) {
      $route = $collection->get('user_registrationpassword.confirm');
      $route->setDefaults([
        '_controller' => '\Drupal\om_social_auth\Controller\OmVerificationController::confirmAccount',
      ]);
    }

  }

}
