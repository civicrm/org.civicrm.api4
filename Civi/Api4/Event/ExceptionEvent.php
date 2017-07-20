<?php

namespace Civi\Api4\Event;

use Civi\Api4\Response;
use Symfony\Component\EventDispatcher\Event;

class ExceptionEvent extends Event {

  /**
   * @var \Exception
   */
  protected $exception;

  /**
   * @var bool
   */
  protected $isDebug;

  /**
   * @var Response
   */
  protected $response;

  /**
   * @param \Exception $exception
   *   The exception which arose while processing the API request.
   * @param bool $isDebug
   *   Whether debug mode is on or not.
   */
  public function __construct($exception, $isDebug = FALSE) {
    $this->exception = $exception;
    $this->isDebug = $isDebug;
  }

  /**
   * @return \Exception
   */
  public function getException() {
    return $this->exception;
  }

  /**
   * @return Response
   */
  public function getResponse() {
    return $this->response;
  }

  /**
   * @return bool
   */
  public function hasResponse() {
    return NULL !== $this->getResponse();
  }

  /**
   * @param Response $response
   *
   * @return $this
   */
  public function setResponse($response) {
    $this->response = $response;

    return $this;
  }

  /**
   * @return bool
   */
  public function isDebug() {
    return $this->isDebug;
  }

}
