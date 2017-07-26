<?php

namespace Civi\Api4\Event\Subscriber\Prepare;

use Civi\Api4\Event\PrepareEvent;
use Civi\Api4\Handler\Actions;
use Civi\Api4\ApiRequest;

abstract class AbstractPreCreationSubscriber extends AbstractPrepareSubscriber implements PrepareEventSubscriber {
  /**
   * @param PrepareEvent $event
   */
  public function onApiPrepare(PrepareEvent $event) {
    $request = $event->getApiRequest();
    if ($request->getAction() !== Actions::CREATE) {
      return;
    }

    $this->addDefaultCreationValues($request);
    if ($this->applies($request)) {
      $this->modify($request);
    }
  }

  /**
   * Sets default values common to all creation requests
   *
   * @param ApiRequest $request
   */
  protected function addDefaultCreationValues(ApiRequest $request) {
    if (NULL === $request->get('is_active')) {
      $request->set('is_active', 1);
    }
  }

}
