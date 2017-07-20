<?php

namespace Civi\Api4\Event;

use Civi\Api4\ApiRequest;
use Symfony\Component\EventDispatcher\Event;

class AuthorizeEvent extends Event {

  /**
   * @var bool
   */
  protected $authorized = FALSE;

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
   * Mark the request as authorized.
   */
  public function authorize() {
    $this->authorized = TRUE;
  }

  /**
   * @return ApiRequest
   */
  public function getApiRequest() {
    return $this->apiRequest;
  }

  /**
   * @return bool
   *   TRUE if the request has been authorized.
   */
  public function isAuthorized() {
    return $this->authorized;
  }
}
