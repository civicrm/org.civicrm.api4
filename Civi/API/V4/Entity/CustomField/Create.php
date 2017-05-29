<?php

namespace Civi\API\V4\Entity\CustomField;

use Civi\API\Result;
use Civi\API\V4\Action;
use \CRM_Utils_Array as ArrayHelper;

class Create extends Action\Create {
  /**
   * @param Result $result
   */
  public function _run(Result $result) {

    $optionType = ArrayHelper::value('option_type', $this->values);
    if (!$optionType) { // default to NULL
      $this->setValue('option_type', NULL);
    }

    parent::_run($result);
  }
}
