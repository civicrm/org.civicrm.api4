<?php

namespace Civi\API\Spec\Provider;

use Civi\API\Spec\RequestSpec;

interface SpecProviderInterface {
  /**
   * @param RequestSpec $specification
   *
   * @return void
   */
  public function modifySpec(RequestSpec $specification);

  /**
   * @param string $entity
   * @param string $action
   *
   * @return bool
   */
  public function applies($entity, $action);
}
