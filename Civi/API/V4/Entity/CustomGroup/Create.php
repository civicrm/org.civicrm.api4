<?php

namespace Civi\API\V4\Entity\CustomGroup;

use Civi\API\Result;
use Civi\API\V4\Action;

class Create extends Action\Create {
  /**
   * @param Result $result
   */
  public function _run(Result $result) {
    $extends = $this->getValue('extends');
    if (is_string($extends)) {
      $this->setValue('extends', array($extends));
    }

    if (NULL === $this->getValue('is_active')) {
      $this->setValue('is_active', 1);
    }

    parent::_run($result);
  }

}
