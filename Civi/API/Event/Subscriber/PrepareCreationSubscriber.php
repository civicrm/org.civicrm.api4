<?php

namespace Civi\API\Event\Subscriber;

use Civi\API\Event\PrepareEvent;
use Civi\API\V4\Action\Create;

abstract class PrepareCreationSubscriber Extends AbstractPrepareSubscriber {
  /**
   * @param PrepareEvent $event
   */
  public function onApiPrepare($event) {
    $apiRequest = $event->getApiRequest();
    if (!$apiRequest instanceof Create) {
      return;
    }

    $this->addDefaultCreationValues($apiRequest);
    $this->modify($apiRequest);
  }

  /**
   * @param Create $request
   *
   * @return void
   */
  abstract protected function modify(Create $request);

  /**
   * @param Create $request
   */
  protected function addDefaultCreationValues(Create $request) {
    if (NULL === $request->getValue('is_active')) {
      $request->setValue('is_active', 1);
    }
  }
}
