<?php

namespace Civi\Api4\Service\Spec\Provider;

use Civi\Api4\Handler\Actions;
use Civi\Api4\Service\Spec\RequestSpec;
use \CRM_Core_DAO_AllCoreTables as TableHelper;

class EntityTagCreationSpecProvider implements SpecProviderInterface {
  /**
   * @inheritdoc
   */
  public function modifySpec(RequestSpec $spec) {
    $field = $spec->getFieldByName('entity_table');
    $field->setRequired(TRUE);
    $field->setOptions(array_keys(TableHelper::getCoreTables()));
  }

  /**
   * @inheritdoc
   */
  public function applies($entity, $action) {
    return $entity === 'EntityTag' && $action === Actions::CREATE;
  }

}
