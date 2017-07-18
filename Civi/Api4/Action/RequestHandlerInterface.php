<?php

namespace Civi\Api4\Action;

use Civi\Api4\Request;
use Civi\Api4\Response;

interface RequestHandlerInterface {

  /**
   * @param Request $request
   *
   * @return Response
   */
  public function handle(Request $request);
}
