<?php

namespace Civi\Api4\Generic;

use Civi\API\Exception\NotImplementedException;

/**
 * Retrieve items based on criteria specified in the 'where' param.
 *
 * Use the 'select' param to determine which fields are returned, defaults to *.
 */
class BasicGetAction extends AbstractGetAction {
  use Traits\ArrayQueryActionTrait;

  /**
   * @var callable
   *
   * Function(BasicGetAction $thisAction) => array<array>
   */
  private $getter;

  /**
   * Basic Get constructor.
   *
   * @param string $entityName
   * @param string $actionName
   * @param callable $getter
   */
  public function __construct($entityName, $actionName, $getter = NULL) {
    parent::__construct($entityName, $actionName);
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
   * 1. Use this class directly by passing a callable ($getter) to the constructor.
   * 2. Extend this class and override this function.
   *
   * Either way, this function should return an array of arrays, each representing one retrieved object.
   *
   * The getter/override for this function will have $this available if it wishes to do any pre-filtering.
   * Depending on the expense of fetching objects that may be worthwhile,
   * but note the WHERE clause can potentially be very complex.
   * Consider using this->_itemsToGet() helper instead of trying to parse $this->where yourself.
   * Be careful not to make assumptions, e.g. if LIMIT 100 is specified and your getter "helpfully" truncates the list
   * at 100 without accounting for the WHERE clause, the final filtered result may be less than expected.
   *
   * $this->select is a simple array of fields to return. If any fields are expensive to retrieve,
   * you can check (!$this->select || in_array('fieldName', $this->select) before doing so.
   * Note that if $this->select is empty you should return every field.
   *
   * @return array
   * @throws \Civi\API\Exception\NotImplementedException
   */
  protected function getRecords() {
    if (is_callable($this->getter)) {
      return call_user_func($this->getter, $this);
    }
    throw new NotImplementedException('Getter function not found for api4 ' . $this->getEntityName() . '::' . $this->getActionName());
  }

  /**
   * Helper to parse the WHERE param for getRecords to perform simple pre-filtering.
   *
   * This is intended to optimize some common use-cases e.g. calling the api to get
   * one or more records by name or id.
   *
   * Ex: If getRecords fetches a long list of items each with a unique name,
   * but the user has specified a single record to retrieve, we can optimize the call
   * by using $this->_itemsToGet('name') and only fetching items requested by name.
   *
   * @param string $field
   * @return array|null
   */
  public function _itemsToGet($field) {
    foreach ($this->where as $clause) {
      if ($clause[0] == $field && in_array($clause[1], ['=', 'IN'])) {
        return (array) $clause[2];
      }
    }
    return NULL;
  }

}
