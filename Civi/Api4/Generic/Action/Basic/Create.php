<?php

namespace Civi\Api4\Generic\Action\Basic;

use Civi\Api4\Generic\Action\AbstractCreate;
use Civi\Api4\Generic\Result;

/**
 * Create a new object from supplied values.
 *
 * This function will create 1 new object. It cannot be used to update existing objects. Use the Update or Replace actions for that.
 */
class Create extends AbstractCreate {

  /**
   * @var callable
   */
  private $setter;

  public function __construct($entity, $setter = NULL) {
    parent::__construct($entity);
    if ($setter) {
      $this->setter = $setter;
    }
    else {
      $this->setter = [$this, 'writeObject'];
    }
  }

  /**
   * We pass the setter function an array representing one object to write.
   * We expect to get the same format back.
   *
   * @param \Civi\Api4\Generic\Result $result
   */
  public function _run(Result $result) {
    $params = $this->getParams();
    unset($params['values']);
    $result->exchangeArray([call_user_func($this->setter, $this->values, $params)]);
  }

}
