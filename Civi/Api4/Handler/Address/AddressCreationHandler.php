<?php

namespace Civi\Api4\Handler\Address;

use Civi\Api4\Handler\CreationHandler;

class AddressCreationHandler extends CreationHandler {

  /**
   * @inheritdoc
   */
  protected function create($entity, $params) {
    /** @var \CRM_Core_BAO_Address $bao */
    $bao = $this->getBAOForEntity($entity);

    return $bao::add($params, TRUE);
  }
}
