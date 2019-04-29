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
   * Only return the number of found items.
   *
   * @return $this
   */
  public function selectRowCount() {
    $this->select = ['row_count'];
    return $this;
  }

}
