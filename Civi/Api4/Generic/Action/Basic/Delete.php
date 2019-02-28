<?php

namespace Civi\Api4\Generic\Action\Basic;

use Civi\Api4\Generic\Action\AbstractBatch;
use Civi\Api4\Generic\Result;

/**
 * Delete one or more items, based on criteria specified in Where param (required).
 */
class Delete extends AbstractBatch {

  /**
   * @var callable
   */
  private $deleter;

  public function __construct($entity, $deleter = NULL, $idField = 'id') {
    parent::__construct($entity, $idField);
    if ($deleter) {
      $this->deleter = $deleter;
    }
    else {
      $this->deleter = [$this, 'deleteObject'];
    }
  }

  /**
   * We pass the setter function an array representing one object to delete.
   * Its return value is ignored.
   *
   * @param \Civi\Api4\Generic\Result $result
   */
  public function _run(Result $result) {
    $params = $this->getParams();
    $items = $this->getBatchItems();

    foreach ($items as &$item) {
      call_user_func($this->deleter, $item, $params);
    }

    $result->exchangeArray($items);
  }

}
