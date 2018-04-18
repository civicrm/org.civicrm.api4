<?php

namespace Civi\Api4\Service\Spec\Provider;

use Civi\Api4\Action\Actions;
use Civi\Api4\Service\Spec\RequestSpec;

class ContributionCreationSpecProvider implements SpecProviderInterface
{
  /**
   * @inheritDoc
   */
    public function modifySpec(RequestSpec $spec)
    {
        $spec->getFieldByName('financial_type_id')->setRequired(true);
    }

  /**
   * @inheritDoc
   */
    public function applies($entity, $action)
    {
        return $entity === 'Contribution' && $action === Actions::CREATE;
    }
}
