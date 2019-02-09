<?php

namespace Civi\Api4\Action\Contribution;

use Civi\Api4\Generic\Result;
use Civi\Api4\Action\Create as DefaultCreate;

/**
 * @inheritDoc
 */
class Create extends DefaultCreate {

  public function _run(Result $result) {
    // Required by Contribution BAO
    $this->values['skipCleanMoney'] = TRUE;
    parent::_run($result);
  }

}
