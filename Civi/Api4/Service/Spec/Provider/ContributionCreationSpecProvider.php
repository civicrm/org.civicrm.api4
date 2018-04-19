<?php

namespace Civi\Api4\Service\Spec\Provider;

use Civi\Api4\Action\Actions;
use Civi\Api4\Service\Spec\RequestSpec;

/**
 * Class ContributionCreationSpecProvider.
 */
class ContributionCreationSpecProvider implements SpecProviderInterface {

  /**
   * @param string $entity
   * @param string $action
   *
   * @return bool
   */
  public function applies($entity, $action) {
    return 'Contribution' === $entity && Actions::CREATE === $action;
  }

  /**
   * @param \Civi\Api4\Service\Spec\RequestSpec $spec
   */
  public function modifySpec(RequestSpec $spec) {
    $spec->getFieldByName('financial_type_id')->setRequired(TRUE);
  }

}
