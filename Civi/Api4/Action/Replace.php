<?php

namespace Civi\Api4\Action;

use Civi\Api4\Generic\Result;

/**
 * Given a set of records, will appropriately update the database.
 *
 * @method $this setRecords(array $records) Array of records.
 * @method $this addRecord($record) Add a record to update.
 */
class Replace extends Get {

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
    $this->setSelect([$this->idField]);

    // First run the parent action (get)
    parent::_run($result);

    $toDelete = (array) $result->indexBy($this->idField);
    $saved = [];

    // Save all items
    foreach ($this->records as $idx => $record) {
      $saved[] = $this->writeObject($record);
      if (!empty($record[$this->idField])) {
        unset($toDelete[$record[$this->idField]]);
      }
    }

    if ($toDelete) {
      civicrm_api4($this->getEntity(), 'Delete', ['where' => [[$this->idField, 'IN', array_keys($toDelete)]]]);
    }
    $result->deleted = array_keys($toDelete);
    $result->exchangeArray($saved);
  }

  /**
   * @inheritDoc
   */
  public function getParamInfo($param = NULL) {
    $info = parent::getParamInfo($param);
    if (!$param) {
      // This action doesn't actually let you select fields.
      unset($info['select']);
    }
    return $info;
  }

}
