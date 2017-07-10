<?php

namespace Civi\API\V4\Service\Spec\Provider;

use Civi\API\V4\Action\Actions;
use Civi\API\V4\Service\Spec\RequestSpec;

class ContributionCreationSpecProvider implements SpecProviderInterface {
  /**
   * @inheritdoc
   */
  public function modifySpec(RequestSpec $spec) {
    $spec->getFieldByName('financial_type_id')->setRequired(true);
  }

  /**
   * @inheritdoc
   */
  public function applies($entity, $action) {
    return $entity === 'Contribution' && $action === Actions::CREATE;
  }
}
