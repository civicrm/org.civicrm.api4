<?php

namespace Civi\Api4\Action\EntityTag;

use Civi\Api4\Action\Create as BaseCreate;

class Create extends BaseCreate {
  /**
   * @inheritdoc
   */
  protected function create($params) {
    return $this->bao->add($params);
  }
}
