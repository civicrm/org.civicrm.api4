<?php

namespace Civi\API\Spec\Provider;

use Civi\API\Spec\RequestSpec;
use Civi\API\V4\Action;

interface SpecProviderInterface {
  /**
   * @param RequestSpec $specification
   *
   * @return void
   */
  public function modifySpec(RequestSpec $specification);

  /**
   * @param Action $request
   *
   * @return bool
   */
  public function applies(Action $request);
}
