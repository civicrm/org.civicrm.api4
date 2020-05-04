<?php

namespace Civi\Api4\Action\Contribution;

use Civi\Api4\Generic\Result;

/**
 * @inheritDoc
 */
class Update extends \Civi\Api4\Generic\DAOUpdateAction {

  public function _run(Result $result) {
    // Required by Contribution BAO
    $this->values['skipCleanMoney'] = TRUE;
    parent::_run($result);
  }

}
