<?php
namespace Drupal\om_utils\EventSubscriber;

use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RedirectByFieldSubscriber implements EventSubscriberInterface {

  public function checkForRedirect(GetResponseEvent $event) {

    // Redirects only non-admin users so admins can see the page
    // and edit if necessary.
    if(\Drupal::currentUser()->hasPermission('administer nodes')){
      return;
    }

    $attr = $event->getRequest()->attributes;
    if($attr && $attr->get('node')){
      /** @var Node $node */
      $node = $attr->get('node');
      if($node->hasField('field_redirect') && !$node->get('field_redirect')->isEmpty()){
        /** @var Url $url */
        $url = $node->get('field_redirect')->first()->getUrl();
        $redirect = new TrustedRedirectResponse($url->setAbsolute(true)->toString(), 301);
        $redirect->addCacheableDependency($node);
        $event->setResponse($redirect);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = array('checkForRedirect');
    return $events;
  }

}