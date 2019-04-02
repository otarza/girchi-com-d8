<?php

namespace Drupal\om_social_auth\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\user\UserStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * User registration password controller class.
 */
class OmVerificationController extends ControllerBase {

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The user storage.
   *
   * @var \Drupal\user\UserStorageInterface
   */
  protected $userStorage;

  /**
   * The entity query.
   *
   * @var \Drupal\Core\Entity\Query\QueryInterface
   */
  protected $entityQuery;

  /**
   * Constructs a UserController object.
   *
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   * @param \Drupal\user\UserStorageInterface $user_storage
   *   The user storage.
   * @param \Drupal\Core\Entity\Query\QueryInterface $entity_query
   *   The Entity Query.
   */
  public function __construct(
    DateFormatterInterface $date_formatter,
    UserStorageInterface $user_storage,
    QueryInterface $entity_query
  ) {
    $this->dateFormatter = $date_formatter;
    $this->userStorage = $user_storage;
    $this->entityQuery = $entity_query;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('date.formatter'),
      $container->get('entity.manager')->getStorage('user'),
      $container->get('entity.query')->get('user', 'AND')
    );
  }


  /**
   * Confirms a user account.
   *
   * @param int $uid
   *   UID of user requesting confirmation.
   * @param int $timestamp
   *   The current timestamp.
   * @param string $hash
   *   Login link hash.
   *
   * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
   *   The form structure or a redirect response.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
   *   If the login link is for a blocked user or invalid user ID.
   */
  public function confirmAccount($uid, $timestamp, $hash) {
    /*
     * შევცვალოთ მოდულის ექშენი როდესაც მომხმარებელი ეცდება აქაუნთის ვერიფიცირებას
     * ჩვენთვის შესაბამისი ლოგიკით.
     * */
    $route_name = '<front>';
    $route_options = [];

    $timeout = $this->config('user_registrationpassword.settings')
      ->get('registration_ftll_timeout');
    $current = \Drupal::time()->getRequestTime();
    $timestamp_created = $timestamp - $timeout;

    $users = $this->entityQuery
      ->condition('uid', $uid)
      ->execute();

    /** @var \Drupal\user\UserInterface $account */
    if ($timestamp_created <= $current && !empty($users) && $account = $this->userStorage->load(reset($users))) {
      // Check if we have to enforce expiration for activation links.
      if ($this->config('user_registrationpassword.settings')
          ->get('registration_ftll_expire') && $current - $timestamp > $timeout) {
        $route_name = user_registrationpassword_set_message('linkerror',
          TRUE);
      }

      elseif ($account->id() && $timestamp >= $account->getCreatedTime() && $hash == user_pass_rehash($account,
          $timestamp)) {
        // Format the date, so the logs are a bit more readable.
        $date = $this->dateFormatter->format($timestamp);
        $this->getLogger('user')
          ->notice('User %name used one-time login link at time %timestamp.',
            ['%name' => $account->getAccountName(), '%timestamp' => $date]);

        /*
         * ვერიფიკაციის წარმატებით გავლის შემდეგ დავუმატოთ მომხმარებელს როლი active
         * და წავუშალოთ როლი passive
         * */
        if (!$account->hasRole('active')) {
          $account->addRole('active');
        }
        if ($account->hasRole('passive')) {
          $account->removeRole('passive');
        }
        $account->save();

        user_login_finalize($account);

        /*
         * დავუსეტოთ ჩვენთვის სასურველი მესიჯი და დავარედაირექტოთ მომხმარებლის გვერდზე
         * */
        drupal_set_message(t('Your account is now active and you are authenticated.'));

        $route_name = 'entity.user.canonical';
        $route_options = ['user' => $account->id()];
      }
      // Something else is wrong, redirect to the password
      // reset form to request a new activation e-mail.
      else {
        $route_name = user_registrationpassword_set_message('linkerror',
          TRUE);
      }
    }
    else {
      $route_name = user_registrationpassword_set_message('linkerror', TRUE);
    }


    return $this->redirect($route_name, $route_options);
  }
}
