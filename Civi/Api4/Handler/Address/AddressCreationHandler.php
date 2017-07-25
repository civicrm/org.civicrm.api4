<?php

namespace Civi\Api4\Handler\Address;

use Civi\Api4\Handler\CreationHandler;
use Civi\Api4\Utils\BAOFinder;

class AddressCreationHandler extends CreationHandler {

  /**
   * @inheritdoc
   */
  protected function create($entity, $params) {
    /** @var \CRM_Core_BAO_Address $bao */
    $bao = BAOFinder::getBAOForEntity($entity);

    return $bao::add($params, TRUE);
  }
}
