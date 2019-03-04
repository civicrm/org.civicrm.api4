<?php

namespace Civi\Api4\Generic;

use Civi\Api4\Generic\Result;

/**
 * Update one or more records with new values.
 *
 * Use the where clause (required) to select them.
 */
class BasicUpdateAction extends AbstractUpdateAction {

  /**
   * @var callable
   */
  private $setter;

  public function __construct($entityName, $actionName, $setter = NULL, $idField = 'id') {
    parent::__construct($entityName, $actionName, $idField);
    $this->setter = $setter;
  }

  /**
   * We pass the setter function an array representing one object to update.
   * We expect to get the same format back.
   *
   * @param \Civi\Api4\Generic\Result $result
   */
  public function _run(Result $result) {
    $items = $this->getBatchRecords();

    foreach ($items as $item) {
      $result[] = $this->writeRecord($this->values + $item);
    }
  }

  /**
   * This Basic Update class can be used in one of two ways:
   *
   * 1. Use this class directly by passing a callable setter from the Entity class.
   * 2. Extend this class and override this function.
   *
   * Either way, this function should return an array representing the one modified object.
   *
   * @param array $item
   * @return array
   */
  protected function writeRecord($item) {
    return call_user_func($this->setter, $item, $this);
  }

}