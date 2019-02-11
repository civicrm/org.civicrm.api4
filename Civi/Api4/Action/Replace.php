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

    // Copy params from where clause if the operator is =
    $paramsFromWhere = [];
    foreach ($this->where as $clause) {
      if (is_array($clause) && $clause[1] === '=') {
        $paramsFromWhere[$clause[0]] = $clause[2];
      }
    }

    $toDelete = array_column($items, NULL, $this->idField);
    foreach ($this->records as &$record) {
      $record += $paramsFromWhere;
      if (!empty($record[$this->idField])) {
        unset($toDelete[$record[$this->idField]]);
      }
    }

    $result->exchangeArray($this->writeObjects($this->records));

    $result->deleted = $this->deleteObjects($toDelete);
  }

}
