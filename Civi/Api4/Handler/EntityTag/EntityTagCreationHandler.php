<?php

namespace Civi\Api4\Handler\EntityTag;

use Civi\Api4\Handler\CreationHandler;

class EntityTagCreationHandler extends CreationHandler {
  /**
   * @inheritdoc
   */
  protected function create($entity, $params) {
    /** @var \CRM_Core_BAO_EntityTag $bao */
    $bao = $this->getBAOForEntity($entity);

    return $bao::add($params);
  }
}
