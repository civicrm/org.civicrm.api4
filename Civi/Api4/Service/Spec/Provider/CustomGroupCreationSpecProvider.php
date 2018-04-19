<?php

namespace Civi\Api4\Service\Spec\Provider;

use Civi\Api4\Action\Actions;
use Civi\Api4\Service\Spec\RequestSpec;

/**
 * Class CustomGroupCreationSpecProvider.
 */
class CustomGroupCreationSpecProvider implements SpecProviderInterface {

  /**
   * @param string $entity
   * @param string $action
   *
   * @return bool
   */
  public function applies($entity, $action) {
    return 'CustomGroup' === $entity && Actions::CREATE === $action;
  }

  /**
   * @param \Civi\Api4\Service\Spec\RequestSpec $spec
   *
   * @return \Civi\Api4\Service\Spec\FieldSpec|null
   */
  public function modifySpec(RequestSpec $spec) {
    return $spec->getFieldByName('extends')->setRequired(TRUE);
  }

}
