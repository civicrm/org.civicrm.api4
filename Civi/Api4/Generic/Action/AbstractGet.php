<?php

namespace Civi\Api4\Generic\Action;

/**
 * Base class for all "Get" api actions.
 *
 * @package Civi\Api4\Generic
 *
 * @method $this addSelect(string $select)
 * @method $this setSelect(array $selects)
 * @method array getSelect()
 */
abstract class AbstractGet extends AbstractQuery {

  /**
   * Fields to return. Defaults to all non-custom fields.
   *
   * @var array
   */
  protected $select = [];

}
