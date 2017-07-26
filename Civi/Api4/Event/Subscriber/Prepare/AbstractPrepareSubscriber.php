<?php

namespace Civi\Api4\Event\Subscriber\Prepare;

use Civi\Api4\Event\Events;
use Civi\Api4\Event\PrepareEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

abstract class AbstractPrepareSubscriber implements EventSubscriberInterface {
  /**
   * @return array
   */
  public static function getSubscribedEvents() {
    return array(
      Events::PREPARE => 'onApiPrepare'
    );
  }

  /**
   * @param PrepareEvent $event
   */
  abstract public function onApiPrepare(PrepareEvent $event);
}
