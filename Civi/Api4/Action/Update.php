<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.7                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2015                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
 */
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
   * Field by which objects are identified.
   *
   * @var string
   */
  private $idField = 'id';

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

  /**
   * @return string
   */
  protected function getIdField() {
    return $this->idField;
  }

  /**
   * @param string $idField
   */
  protected function setIdField($idField) {
    $this->idField = $idField;
  }

}
