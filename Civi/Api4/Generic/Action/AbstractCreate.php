<?php

namespace Civi\Api4\Generic\Action;

/**
 * Base class for all "Create" api actions.
 *
 * @method $this setValues(array $values) Set all field values from an array of key => value pairs.
 * @method $this addValue($field, $value) Set field value.
 * @method array getValues() Get field values.
 *
 * @package Civi\Api4\Generic
 */
abstract class AbstractCreate extends AbstractAction {

  /**
   * Field values to set
   *
   * @var array
   */
  protected $values = [];

  /**
   * @param $key
   *
   * @return mixed|null
   */
  public function getValue($key) {
    return isset($this->values[$key]) ? $this->values[$key] : NULL;
  }

}
