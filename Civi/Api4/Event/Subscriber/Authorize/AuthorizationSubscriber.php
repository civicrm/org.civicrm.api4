<?php

namespace Civi\Api4\Event\Subscriber\Authorize;

use Civi\Api4\Event\AuthorizeEvent;
use Civi\Api4\Event\Events;
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
    $event->authorize(); // todo authorization
  }
}
