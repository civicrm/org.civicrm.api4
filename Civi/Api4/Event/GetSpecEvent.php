<?php

namespace Civi\Api4\Event;

use Civi\Api4\RequestHandler;
use Symfony\Component\EventDispatcher\Event as BaseEvent;

class GetSpecEvent extends BaseEvent {
  /**
   * @var RequestHandler
   */
  protected $request;

  /**
   * @param RequestHandler $request
   */
  public function __construct(RequestHandler $request) {
    $this->request = $request;
  }

  /**
   * @return RequestHandler
   */
  public function getRequest() {
    return $this->request;
  }

  /**
   * @param $request
   */
  public function setRequest(RequestHandler $request) {
    $this->request = $request;
  }
}
