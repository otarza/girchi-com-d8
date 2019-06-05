<?php

namespace Drupal\om_social_auth\EventSubscriber;

use Drupal\social_api\Plugin\NetworkManager;
use Drupal\social_auth\Event\SocialAuthEvents;
use Drupal\social_auth\Event\SocialAuthUserEvent;
use Drupal\social_auth_facebook\FacebookAuthManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Class SocialAuthSubscriber.
 */
class SocialAuthSubscriber implements EventSubscriberInterface {

  /**
   * Drupal\Core\Session\SessionManager definition.
   *
   * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
   */
  protected $session;

  /**
   * Drupal\social_api\Plugin\NetworkManager definition.
   *
   * @var \Drupal\social_api\Plugin\NetworkManager
   */
  protected $pluginNetworkManager;

  /**
   * Drupal\social_auth_facebook\FacebookAuthManager definition.
   *
   * @var \Drupal\social_auth_facebook\FacebookAuthManager
   */
  protected $socialAuthFacebookManager;

  /**
   * Constructs a new SocialAuthSubscriber object.
   *
   * @param \Symfony\Component\HttpFoundation\Session\Session $session
   * @param \Drupal\social_api\Plugin\NetworkManager $plugin_network_manager
   * @param \Drupal\social_auth_facebook\FacebookAuthManager $social_auth_facebook_manager
   */
  public function __construct(
    Session $session,
    NetworkManager $plugin_network_manager,
    FacebookAuthManager $social_auth_facebook_manager
  ) {
    $this->session = $session;
    $this->pluginNetworkManager = $plugin_network_manager;
    $this->socialAuthFacebookManager = $social_auth_facebook_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[SocialAuthEvents::USER_CREATED] = ['onUserCreated'];
    $events[SocialAuthEvents::USER_LOGIN] = ['onUserLogin'];

    return $events;
  }

  /**
   * Alters the user name.
   *
   * @param \Drupal\social_auth\Event\SocialAuthUserEvent $event
   *   The Social Auth user event object.
   */
  public function onUserCreated(SocialAuthUserEvent $event) {
    /**
     * @var \Drupal\user\UserInterface $user
     */
    $user = $event->getUser();
  }

  /**
   * Sets a drupal message when a user logs in.
   *
   * @param \Drupal\social_auth\Event\SocialAuthUserEvent $event
   *   The Social Auth user event object.
   */
  public function onUserLogin(SocialAuthUserEvent $event) {
    /**
     * @var \Drupal\user\UserInterface $user
     */
    $user = $event->getUser();
    if ($user->hasRole('passive')) {
      $user->removeRole('passive');
      $user->addRole('active');
      $user->save();
    }
  }

}
