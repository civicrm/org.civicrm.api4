<?php

namespace Civi\Api4\Event\Subscriber\Authorize;

use Civi\Api4\Event\AuthorizeEvent;
use Civi\Api4\Event\Events;
use Civi\Api4\Handler\Actions;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AuthorizationSubscriber implements EventSubscriberInterface {

  /**
   * @inheritdoc
   */
  public static function getSubscribedEvents() {
    return array(
      Events::AUTHORIZE => 'onApiAuthorize'
    );
  }

  /**
   * @param AuthorizeEvent $event
   */
  public function onApiAuthorize(AuthorizeEvent $event) {

    $apiRequest = $event->getApiRequest();

    if (FALSE === $apiRequest->getCheckPermissions()) {
      $event->authorize();
    }

    $publicActions = array(Actions::GET_FIELDS);
    if (in_array($apiRequest->getAction(), $publicActions)) {
      $event->authorize();
    }
  }
}
