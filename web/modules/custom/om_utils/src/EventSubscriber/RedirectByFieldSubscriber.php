<?php

namespace Drupal\om_utils\EventSubscriber;

use Drupal\Core\Routing\TrustedRedirectResponse;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Redirect by field.
 */
class RedirectByFieldSubscriber implements EventSubscriberInterface {

  /**
   *
   */
  public function checkForRedirect(GetResponseEvent $event) {

    // Redirects only non-admin users so admins can see the page
    // and edit if necessary.
    if (\Drupal::currentUser()->hasPermission('administer nodes')) {
      return;
    }

    $attr = $event->getRequest()->attributes;
    if ($attr && $attr->get('node')) {
      /** @var \Drupal\node\Entity\Node $node */
      $node = $attr->get('node');
      if ($node->hasField('field_redirect') && !$node->get('field_redirect')->isEmpty()) {
        /** @var \Drupal\Core\Url $url */
        $url = $node->get('field_redirect')->first()->getUrl();
        $redirect = new TrustedRedirectResponse($url->setAbsolute(TRUE)->toString(), 301);
        $redirect->addCacheableDependency($node);
        $event->setResponse($redirect);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['checkForRedirect'];
    return $events;
  }

}
