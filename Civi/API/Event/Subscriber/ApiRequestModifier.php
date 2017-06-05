<?php

namespace Civi\API\Event\Subscriber;

use Civi\API\Event\PrepareEvent;
use Civi\API\Events;
use Civi\API\V4\Action\Create;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

abstract class ApiRequestModifier implements EventSubscriberInterface {
  /**
   * @param PrepareEvent $event
   */
  public function onApiPrepare($event) {
    $apiRequest = $event->getApiRequest();
    if (!$apiRequest instanceof Create) {
      return;
    }
    $this->modify($apiRequest);
  }

  /**
   * @param Create $request
   *
   * @return void
   */
  abstract protected function modify(Create $request);

  /**
   * @return array
   */
  public static function getSubscribedEvents() {
    return array(
      Events::PREPARE => 'onApiPrepare'
    );
  }
}
