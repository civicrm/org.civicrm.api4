<?php

namespace Civi\Api4\Service\Spec\Provider;

use Civi\Api4\Action\Actions;
use Civi\Api4\Service\Spec\RequestSpec;

class CustomGroupCreationSpecProvider implements SpecProviderInterface {
  /**
   * @inheritdoc
   */
  public function modifySpec(RequestSpec $spec) {
    return $spec->getFieldByName('extends')->setRequired(TRUE);
  }

  /**
   * @inheritdoc
   */
  public function applies($entity, $action) {
    return $entity === 'CustomGroup' && $action === Actions::CREATE;
  }

}
