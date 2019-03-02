<?php

namespace Civi\Api4\Event\Subscriber;

use Civi\API\Event\PrepareEvent;
use Civi\Api4\Generic\Action\DAOCreate;

abstract class PreCreationSubscriber extends AbstractPrepareSubscriber {
  /**
   * @param PrepareEvent $event
   */
  public function onApiPrepare(PrepareEvent $event) {
    $apiRequest = $event->getApiRequest();
    if (!$apiRequest instanceof DAOCreate) {
      return;
    }

    $this->addDefaultCreationValues($apiRequest);
    if ($this->applies($apiRequest)) {
      $this->modify($apiRequest);
    }
  }

  /**
   * Modify the request
   *
   * @param DAOCreate $request
   *
   * @return void
   */
  abstract protected function modify(DAOCreate $request);

  /**
   * Check if this subscriber should be applied to the request
   *
   * @param DAOCreate $request
   *
   * @return bool
   */
  abstract protected function applies(DAOCreate $request);

  /**
   * Sets default values common to all creation requests
   *
   * @param DAOCreate $request
   */
  protected function addDefaultCreationValues(DAOCreate $request) {
    if (NULL === $request->getValue('is_active')) {
      $request->addValue('is_active', 1);
    }
  }

}
