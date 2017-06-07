<?php

namespace Civi\API\Event\Subscriber;

use Civi\API\Event\Events;
use Civi\API\Event\GetSpecEvent;
use Civi\API\Event\PrepareEvent;
use Civi\API\V4\Action;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class GetFieldsSubscriber extends AbstractPrepareSubscriber {

  /**
   * @var EventDispatcherInterface
   */
  protected $dispatcher;

  /**
   * @param EventDispatcherInterface $dispatcher
   */
  public function __construct(EventDispatcherInterface $dispatcher) {
    $this->dispatcher = $dispatcher;
  }

  /**
   * @param PrepareEvent $event
   */
  public function onApiPrepare($event) {
    /** @var Action $request */
    $request = $event->getApiRequest();
    if (!$request instanceof Action || $request->getAction() !== 'getFields') {
      return;
    }

    $event = new GetSpecEvent($request);
    $this->dispatcher->dispatch(Events::GET_SPEC, $event);
  }
}
