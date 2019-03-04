<?php

namespace Civi\Api4\Generic\Action;

use Civi\Api4\Generic\Result;

/**
 * Delete one or more items, based on criteria specified in Where param (required).
 */
class BasicDelete extends AbstractBatch {

  /**
   * @var callable
   */
  private $deleter;

  public function __construct($entity = NULL, $deleter = NULL, $idField = 'id') {
    parent::__construct($entity, $idField);
    $this->deleter = $deleter;
  }

  /**
   * We pass the setter function an array representing one object to delete.
   * Its return value is ignored.
   *
   * @param \Civi\Api4\Generic\Result $result
   */
  public function _run(Result $result) {
    $items = $this->getBatchRecords();

    foreach ($items as &$item) {
      $this->deleteRecord($item);
    }

    $result->exchangeArray($items);
  }

  /**
   * This Basic Delete class can be used in one of two ways:
   *
   * 1. Use this class directly by passing a callable deleter from the Entity class.
   * 2. Extend this class and override this function.
   *
   * Either way, this function should return an array representing the one new object.
   *
   * @param array $item
   * @return array
   */
  protected function deleteRecord($item) {
    return call_user_func($this->deleter, $item, $this);
  }

  /**
   * @return string
   */
  public function getActionName() {
    return 'delete';
  }

}
