<?php

namespace Civi\Api4\Event\Subscriber;

use Civi\API\Event\PrepareEvent;
use Civi\Api4\Handler\Actions;
use Civi\Api4\Request;

abstract class AbstractPreGetSubscriber extends AbstractPrepareSubscriber {

  public function onApiPrepare(PrepareEvent $event) {
    $request = $event->getApiRequest();
    if (!$request instanceof Request || $request->getAction() !== Actions::GET) {
      return;
    }

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
}
