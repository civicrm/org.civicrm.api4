<?php

namespace Civi\Api4\Event\Subscriber;

use Civi\API\Event\PrepareEvent;
use Civi\Api4\Handler\CreationHandler;
use Civi\Api4\Request;

abstract class AbstractPreCreationSubscriber extends AbstractPrepareSubscriber {
  /**
   * @param PrepareEvent $event
   */
  public function onApiPrepare(PrepareEvent $event) {
    $request = $event->getApiRequest();
    if (!$request instanceof Request || !$request->getHandler() instanceof CreationHandler) {
      return;
    }

    $this->addDefaultCreationValues($request);
    if ($this->applies($request)) {
      $this->modify($request);
    }
  }

  /**
   * Modify the request
   *
   * @param Request $request
   *
   * @return void
   */
  abstract protected function modify(Request $request);

  /**
   * Check if this subscriber should be applied to the request
   *
   * @param Request $request
   *
   * @return bool
   */
  abstract protected function applies(Request $request);

  /**
   * Sets default values common to all creation requests
   *
   * @param Request $request
   */
  protected function addDefaultCreationValues(Request $request) {
    if (NULL === $request->get('is_active')) {
      $request->set('is_active', 1);
    }
  }

}
