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
    $this->setSelect([$this->idField]);
    // For some reason the contact bao requires this
    if ($this->getEntity() == 'Contact') {
      $this->select[] = 'contact_type';
    }

    $items = $this->getObjects();

    foreach ($items as $item) {
      $result[] = $this->writeObject($this->values + $item);
    }
  }

  /**
   * @inheritDoc
   */
  public function getParamInfo($param = NULL) {
    $info = parent::getParamInfo($param);
    if (!$param) {
      // Update doesn't actually let you select fields.
      unset($info['select']);
    }
    return $info;
  }

}
