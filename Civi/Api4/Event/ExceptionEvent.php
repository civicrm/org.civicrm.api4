<?php

namespace Civi\Api4\Event;

use Symfony\Component\EventDispatcher\Event;

class ExceptionEvent extends Event {

  /**
   * @var \Exception
   */
  private $exception;

  /**
   * @param \Exception $exception
   *   The exception which arose while processing the API request.
   */
  public function __construct($exception) {
    $this->exception = $exception;
  }

  /**
   * @return \Exception
   */
  public function getException() {
    return $this->exception;
  }

}
