<?php

namespace Civi\Api4\Generic\Action;

use Civi\Api4\Generic\Result;

/**
 * Retrieve items based on criteria specified in the 'where' param.
 *
 * Use the 'select' param to determine which fields are returned, defaults to *.
 */
class BasicGet extends AbstractGet {
  use \Civi\Api4\Generic\Action\Traits\ArrayQueryTrait;

  /**
   * @var callable
   */
  private $getter;

  public function __construct($entity = NULL, $getter = NULL) {
    parent::__construct($entity);
    $this->getter = $getter;
  }

  /**
   * Fetch results from the getter then apply filter/sort/select/limit.
   *
   * @param \Civi\Api4\Generic\Result $result
   */
  public function _run(Result $result) {
    $values = $this->getRecords();
    $result->exchangeArray($this->queryArray($values));
  }

  /**
   * This Basic Get class can be used in one of two ways:
   *
   * 1. Use this class directly by passing a callable getter from the Entity class.
   * 2. Extend this class and override this function.
   *
   * Either way, this function should return an array of arrays, each representing one retrieved object.
   *
   * The getter/override for this function will have $this available if it wishes to do any pre-filtering.
   * Depending on the expense of fetching objects that may be worthwhile,
   * but note the WHERE clause can potentially be very complex.
   * Be careful not to make assumptions, e.g. if LIMIT 100 is specified and your getter "helpfully" truncates the list
   * at 100 without accounting for the WHERE clause, the final filtered result may be less than expected.
   * $this->select is a simple array of fields to return. If any fields are expensive to retrieve,
   * you can check (!$this->select || in_array('fieldName', $this->select) before doing so.
   * Note that if $this->select is empty you should return every field.
   *
   * @return array
   */
  protected function getRecords() {
    return call_user_func($this->getter, $this);
  }

  /**
   * @return string
   */
  public function getActionName() {
    return 'get';
  }

}
