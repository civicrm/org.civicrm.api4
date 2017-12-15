<?php

namespace Civi\Api4\Service\Spec\Provider;

use Civi\Api4\Action\Actions;
use Civi\Api4\Service\Spec\FieldSpec;
use Civi\Api4\Service\Spec\RequestSpec;

class ActivityCreationSpecProvider implements SpecProviderInterface {
  /**
   * @inheritDoc
   */
  public function modifySpec(RequestSpec $spec) {
    $spec->getFieldByName('subject')->setRequired(TRUE);

    $sourceContactField = new FieldSpec('source_contact_id', 'Integer');
    $sourceContactField->setRequired(TRUE);
    $sourceContactField->setFkEntity('Contact');

    $spec->addFieldSpec($sourceContactField);
  }

  /**
   * @inheritDoc
   */
  public function applies($entity, $action) {
    return $entity === 'Activity' && $action === Actions::CREATE;
  }

}
