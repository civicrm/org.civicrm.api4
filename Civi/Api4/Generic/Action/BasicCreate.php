<?php

namespace Civi\Api4\Generic\Action;

use Civi\Api4\Generic\Result;

/**
 * Create a new object from supplied values.
 *
 * This function will create 1 new object. It cannot be used to update existing objects. Use the Update or Replace actions for that.
 */
class BasicCreate extends AbstractCreate {

  /**
   * @var callable
   */
  private $setter;

  public function __construct($entity, $setter = NULL) {
    parent::__construct($entity);
    $this->setter = $setter;
  }

  /**
   * We pass the setter function an array representing one object to write.
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
   * 1. Use this class directly by passing a callable setter from the Entity class.
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

  /**
   * @return string
   */
  public function getActionName() {
    return 'create';
  }

}
