<?php

namespace Civi\Api4\Event\Subscriber\Prepare;

use Civi\Api4\Event\PrepareEvent;
use Civi\Api4\Handler\Actions;

abstract class AbstractPreGetSubscriber extends AbstractPrepareSubscriber implements PrepareEventSubscriber {

  /**
   * @inheritdoc
   */
  public function onApiPrepare(PrepareEvent $event) {
    $request = $event->getApiRequest();
    if ($request->getAction() !== Actions::GET) {
      return;
    }

    if ($this->applies($request)) {
      $this->modify($request);
    }
  }
}
