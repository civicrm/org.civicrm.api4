<?php

namespace Civi\Api4\Action;

use Civi\Api4\Generic\Result;

/**
 * Update one or more records with new values.
 *
 * Use the where clause (required) to select them.
 *
 * @method $this setValues(array $values) Set all field values from an array of key => value pairs.
 * @method $this addValue($field, $value) Set field value to update.
 * @method $this setReload(bool $reload) Specify whether complete objects will be returned after saving.
 */
class Update extends Get {

  use \Civi\Api4\Generic\BulkActionTrait;

  /**
   * Fields to be selected by get action
   *
   * @var array
   */
  protected $select = ['id'];

  /**
   * Criteria for selecting items to update.
   *
   * @required
   * @var array
   */
  protected $where = [];

  /**
   * Field values to update.
   *
   * @var array
   */
  protected $values = [];

  /**
   * Reload object after saving.
   *
   * Setting to TRUE will load complete records and return them as the api result.
   * If FALSE the api usually returns only the fields specified to be updated.
   *
   * @var bool
   */
  protected $reload = FALSE;

  /**
   * @param $key
   *
   * @return mixed|null
   */
  public function getValue($key) {
    return isset($this->values[$key]) ? $this->values[$key] : NULL;
  }

  /**
   * @inheritDoc
   */
  public function _run(Result $result) {
    if (!empty($this->values[$this->idField])) {
      throw new \Exception("Cannot update the {$this->idField} of an existing " . $this->getEntity() . '.');
    }

    $items = $this->getObjects();
    foreach ($items as &$item) {
      $item = $this->values + $item;
    }

    $result->exchangeArray($this->writeObjects($items));
  }

}
