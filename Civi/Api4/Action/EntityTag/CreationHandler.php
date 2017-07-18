<?php

namespace Civi\Api4\Action\EntityTag;

use Civi\Api4\Action\CreationHandler as BaseCreate;

class CreationHandler extends BaseCreate {
  /**
   * @inheritdoc
   */
  protected function create($entity, $params) {
    $this->getBAOForEntity($entity)->add($params);
  }
}
