<?php

namespace Civi\API\Event;

use Civi\API\V4\Action;
use Symfony\Component\EventDispatcher\Event as BaseEvent;

class GetSpecEvent extends BaseEvent {
  /**
   * @var Action
   */
  protected $request;

  /**
   * @param Action $request
   */
  public function __construct(Action $request) {
    $this->request = $request;
  }

  /**
   * @return Action
   */
  public function getRequest() {
    return $this->request;
  }

  /**
   * @param $request
   */
  public function setRequest(Action $request) {
    $this->request = $request;
  }
}
