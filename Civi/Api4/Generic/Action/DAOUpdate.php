<?php

namespace Civi\Api4\Generic\Action;

use Civi\Api4\Generic\Result;

/**
 * Update one or more records with new values.
 *
 * Use the where clause (required) to select them.
 */
class DAOUpdate extends AbstractUpdate {
  use Traits\DAOTrait;

  /**
   * @inheritDoc
   */
  public function _run(Result $result) {
    if (!empty($this->values['id'])) {
      throw new \Exception("Cannot update the id of an existing " . $this->getEntityName() . '.');
    }

    $items = $this->getObjects();
    foreach ($items as &$item) {
      $item = $this->values + $item;
    }

    $result->exchangeArray($this->writeObjects($items));
  }

  /**
   * @return string
   */
  public function getActionName() {
    return 'update';
  }

}
