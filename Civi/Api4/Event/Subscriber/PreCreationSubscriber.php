<?php

namespace Civi\Api4\Event\Subscriber;

use Civi\API\Event\PrepareEvent;
use Civi\Api4\Action\Create;

/**
 * Class PreCreationSubscriber.
 */
abstract class PreCreationSubscriber extends AbstractPrepareSubscriber {

  /**
   * @param \Civi\API\Event\PrepareEvent $event
   */
  public function onApiPrepare(PrepareEvent $event) {
    $apiRequest = $event->getApiRequest();
    if (!$apiRequest instanceof Create) {
      return;
    }
    $this->addDefaultCreationValues($apiRequest);
    if ($this->applies($apiRequest)) {
      $this->modify($apiRequest);
    }
  }

  /**
   * Sets default values common to all creation requests.
   *
   * @param \Civi\Api4\Action\Create $request
   */
  protected function addDefaultCreationValues(Create $request) {
    if (NULL === $request->getValue('is_active')) {
      $request->addValue('is_active', 1);
    }
  }

  /**
   * Check if this subscriber should be applied to the request.
   *
   * @param \Civi\Api4\Action\Create $request
   *
   * @return bool
   */
  abstract protected function applies(Create $request);

  /**
   * Modify the request.
   *
   * @param \Civi\Api4\Action\Create $request
   */
  abstract protected function modify(Create $request);

}
