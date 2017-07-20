<?php

namespace Civi\Api4\Event;

use Civi\Api4\Response;
use Symfony\Component\EventDispatcher\Event;

class RespondEvent extends Event {

  /**
   * @var Response
   */
  private $response;

  /**
   * @param Response $response
   *   The response to return to the client.
   */
  public function __construct(Response $response) {
    $this->response = $response;
  }

  /**
   * @return Response
   */
  public function getResponse() {
    return $this->response;
  }

  /**
   * @param Response $response
   *   The response to return to the client.
   */
  public function setResponse(Response $response) {
    $this->response = $response;
  }
}
