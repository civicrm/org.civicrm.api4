<?php

namespace Civi\Api4\Generic;

/**
 * Base class for all "Get" api actions.
 *
 * @package Civi\Api4\Generic
 *
 * @method $this addSelect(string $select)
 * @method $this setSelect(array $selects)
 * @method array getSelect()
 * @method $this addFilter(string $field, string $value)
 * @method $this setFilter(array $filters)
 * @method array getFilter()
 */
abstract class AbstractGetAction extends AbstractQueryAction {

  /**
   * Fields to return. Defaults to all fields.
   *
   * Set to ["row_count"] to return only the number of items found.
   *
   * @var array
   */
  protected $select = [];

  /**
   * Search-style filters.
   *
   * A simple/convenient [field => value] array for using the api in a search context.
   *
   * These will be added to the WHERE clause using the LIKE operator;
   * Wildcards will be added and empty/null values will be ignored.
   *
   * @var array
   */
  protected $filter = [];

  /**
   * Only return the number of found items.
   *
   * @return $this
   */
  public function selectRowCount() {
    $this->select = ['row_count'];
    return $this;
  }

  /**
   * Add filters to the where clause.
   *
   * @throws \API_Exception
   */
  protected function applyFilters() {
    foreach ($this->filter as $field => $value) {
      if ($value !== NULL && $value !== '') {
        $op = is_numeric($value) ? '=' : 'LIKE';
        $value = is_numeric($value) ? $value : '%' . $value . '%';
        $this->addWhere($field, $op, $value);
      }
    }
  }

}
