<?php

namespace Civi\Api4\Action\EntityTag;

use Civi\Api4\Action\Create as BaseCreate;

class Create extends BaseCreate {
  /**
   * @inheritdoc
   */
  protected function getCreationMethodName() {
    return 'add';
  }
}
