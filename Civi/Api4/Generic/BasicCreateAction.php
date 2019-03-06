<?php

namespace Civi\Api4\Generic;

use Civi\Api4\Generic\Result;

/**
 * Create a new object from supplied values.
 *
 * This function will create 1 new object. It cannot be used to update existing objects. Use the Update or Replace actions for that.
 */
class BasicCreateAction extends AbstractCreateAction {

  /**
   * @var callable
   *
   * Function(array $item, BasicCreateAction $thisAction) => array
   */
  private $setter;

  /**
   * Basic Create constructor.
   *
   * @param string $entityName
   * @param string $actionName
   * @param callable $setter
   *   Function(array $item, BasicCreateAction $thisAction) => array
   */
  public function __construct($entityName, $actionName, $setter = NULL) {
    parent::__construct($entityName, $actionName);
    $this->setter = $setter;
  }

  /**
   * We pass the writeRecord function an array representing one item to write.
   * We expect to get the same format back.
   *
   * @param \Civi\Api4\Generic\Result $result
   */
  public function _run(Result $result) {
    $result->exchangeArray([$this->writeRecord($this->values)]);
  }

  /**
   * This Basic Create class can be used in one of two ways:
   *
   * 1. Use this class directly by passing a callable ($setter) to the constructor.
   * 2. Extend this class and override this function.
   *
   * Either way, this function should return an array representing the one new object.
   *
   * @param array $item
   * @return array
   */
  protected function writeRecord($item) {
    return call_user_func($this->setter, $item, $this);
  }

}
