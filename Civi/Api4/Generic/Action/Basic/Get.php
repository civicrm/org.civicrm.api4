<?php

namespace Civi\Api4\Generic\Action\Basic;

use Civi\Api4\Generic\Action\AbstractGet;
use Civi\Api4\Generic\Result;

/**
 * Retrieve items based on criteria specified in the 'where' param.
 *
 * Use the 'select' param to determine which fields are returned, defaults to *.
 */
class Get extends AbstractGet {
  use \Civi\Api4\Generic\Action\Traits\ArrayQueryTrait;

  /**
   * @var callable
   */
  private $getter;

  public function __construct($entity, $getter = NULL) {
    parent::__construct($entity);
    if ($getter) {
      $this->getter = $getter;
    }
    else {
      $this->getter = [$this, 'getObjects'];
    }
  }

  /**
   * We expect an array of arrays from the getter.
   * We pass it the params array in case it wants to do any pre-filtering for performance.
   *
   * @param \Civi\Api4\Generic\Result $result
   */
  public function _run(Result $result) {
    $values = call_user_func($this->getter, $this->getParams());
    $result->exchangeArray($this->queryArray($values));
  }

}
