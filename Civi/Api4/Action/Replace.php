<?php

namespace Civi\Api4\Action;

use Civi\Api4\Generic\Result;

/**
 * Given a set of records, will appropriately update the database.
 *
 * @method $this setRecords(array $records) Array of records.
 * @method $this addRecord($record) Add a record to update.
 */
class Replace extends Delete {

  use \Civi\Api4\Generic\BulkActionTrait;

  /**
   * Fields to be selected by get action
   *
   * @var array
   */
  protected $select = ['id'];

  /**
   * Array of records.
   *
   * @required
   * @var array
   */
  protected $records = [];

  /**
   * @inheritDoc
   */
  public function _run(Result $result) {
    $items = $this->getObjects();

    $toDelete = array_column($items, NULL, $this->idField);
    foreach ($this->records as $record) {
      if (!empty($record[$this->idField])) {
        unset($toDelete[$record[$this->idField]]);
      }
    }

    $result->exchangeArray($this->writeObjects($this->records));

    $result->deleted = $this->deleteObjects($toDelete);
  }

}
