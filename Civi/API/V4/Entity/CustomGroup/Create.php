<?php

namespace Civi\API\V4\Entity\CustomGroup;

use Civi\API\Result;
use Civi\API\V4\Action;
use \CRM_Utils_Array as ArrayHelper;

class Create extends Action\Create {
  /**
   * @param Result $result
   */
  public function _run(Result $result) {
    $extends = ArrayHelper::value('extends', $this->values);
    if (is_string($extends)) {
      $this->setValue('extends', array($extends));
    }

    parent::_run($result);
  }

}
