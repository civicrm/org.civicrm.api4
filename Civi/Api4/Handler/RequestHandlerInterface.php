<?php

namespace Civi\Api4\Handler;

use Civi\Api4\ApiRequest;
use Civi\Api4\Response;

interface RequestHandlerInterface {

  /**
   * @param ApiRequest $request
   *
   * @return Response
   */
  public function handle(ApiRequest $request);

  /**
   * @return string
   *   The name of the action
   */
  public function getAction();
}
