<?php

namespace Civi\Api4\Service\Spec\Provider;

use Civi\Api4\Handler\Actions;
use Civi\Api4\Service\Spec\FieldSpec;
use Civi\Api4\Service\Spec\RequestSpec;

class ActivityCreationSpecProvider implements SpecProviderInterface {
  /**
   * @inheritdoc
   */
  public function modifySpec(RequestSpec $spec) {
    $spec->getFieldByName('subject')->setRequired(true);

    $sourceContactField = new FieldSpec('source_contact_id', 'Integer');
    $sourceContactField->setRequired(true);
    $sourceContactField->setFkEntity('Contact');

    $spec->addFieldSpec($sourceContactField);
  }

  /**
   * @inheritdoc
   */
  public function applies($entity, $action) {
    return $entity === 'Activity' && $action === Actions::CREATE;
  }

}
