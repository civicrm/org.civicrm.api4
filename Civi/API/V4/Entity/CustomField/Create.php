<?php

namespace Civi\API\V4\Entity\CustomField;

use Civi\API\Result;
use Civi\API\V4\Action;

class Create extends Action\Create {
  /**
   * @param Result $result
   */
  public function _run(Result $result) {

    $optionType = $this->getValue('option_type');
    if (!$optionType) { // default to NULL
      $this->setValue('option_type', NULL);
    }

    parent::_run($result);
  }
}
