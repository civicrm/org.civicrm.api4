<?php

namespace Civi\Api4\Generic\Action;

use Civi\Api4\Generic\Result;

/**
 * Retrieve items based on criteria specified in the 'where' param.
 *
 * Use the 'select' param to determine which fields are returned, defaults to *.
 *
 * Perform joins on other related entities using a dot notation.
 */
class DAOGet extends AbstractGet {
  use Traits\DAOTrait;

  public function _run(Result $result) {
    $result->exchangeArray($this->getObjects());
  }

  /**
   * @return string
   */
  public function getActionName() {
    return 'get';
  }

}
