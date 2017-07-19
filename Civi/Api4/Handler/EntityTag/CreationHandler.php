<?php

namespace Civi\Api4\Handler\EntityTag;

use Civi\Api4\Handler\CreationHandler as BaseCreate;

class CreationHandler extends BaseCreate {
  /**
   * @inheritdoc
   */
  protected function create($entity, $params) {
    $this->getBAOForEntity($entity)->add($params);
  }
}
