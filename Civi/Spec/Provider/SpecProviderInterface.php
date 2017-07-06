<?php

namespace Civi\API\V4\Spec\Provider;

use Civi\API\V4\Spec\RequestSpec;

interface SpecProviderInterface {
  /**
   * @param RequestSpec $spec
   *
   * @return void
   */
  public function modifySpec(RequestSpec $spec);

  /**
   * @param string $entity
   * @param string $action
   *
   * @return bool
   */
  public function applies($entity, $action);
}
