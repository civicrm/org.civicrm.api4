<?php

namespace Civi\Api4\Event;

use Civi\Api4\ApiRequest;
use Symfony\Component\EventDispatcher\Event;

class PrepareEvent extends Event {

  /**
   * @var ApiRequest
   */
  protected $apiRequest;

  /**
   * @param ApiRequest $apiRequest
   */
  public function __construct(ApiRequest $apiRequest) {
    $this->apiRequest = $apiRequest;
  }

  /**
   * @return ApiRequest
   */
  public function getApiRequest() {
    return $this->apiRequest;
  }
}
