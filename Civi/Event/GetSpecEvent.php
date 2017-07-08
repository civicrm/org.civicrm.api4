<?php

namespace Civi\API\V4\Event;

use Civi\API\V4\Action\AbstractAction;
use Symfony\Component\EventDispatcher\Event as BaseEvent;

class GetSpecEvent extends BaseEvent {
  /**
   * @var AbstractAction
   */
  protected $request;

  /**
   * @param AbstractAction $request
   */
  public function __construct(AbstractAction $request) {
    $this->request = $request;
  }

  /**
   * @return AbstractAction
   */
  public function getRequest() {
    return $this->request;
  }

  /**
   * @param $request
   */
  public function setRequest(AbstractAction $request) {
    $this->request = $request;
  }
}
