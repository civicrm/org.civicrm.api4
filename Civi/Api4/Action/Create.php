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

use Civi\Api4\Generic\AbstractAction;
use Civi\Api4\Generic\Result;

/**
 * Base class for all create actions.
 *
 * @method $this setValues($values) Set all field values.
 */
class Create extends AbstractAction {

  /**
   * Field values to set
   *
   * @var array
   */
  protected $values = [];

  /**
   * Set a field value for the created object.
   *
   * @param string $key
   * @param mixed $value
   * @return $this
   * @throws \API_Exception
   */
  public function setValue($key, $value) {
    if ($key == 'id') {
      throw new \API_Exception('Cannot update the id of an existing object.');
    }
    $this->values[$key] = $value;
    return $this;
  }

  /**
   * @param $key
   *
   * @return mixed|null
   */
  public function getValue($key) {
    return isset($this->values[$key]) ? $this->values[$key] : NULL;
  }

  /**
   * @return array
   */
  public function getValues() {
    return $this->values;
  }

  /**
   * @inheritDoc
   */
  public function _run(Result $result) {
    if (!empty($this->values['id'])) {
      throw new \API_Exception('Cannot pass id to Create action. Use Update action instead.');
    }

    $resultArray = $this->writeObject($this->values);

    // fixme should return a single row array???
    $result->exchangeArray($resultArray);
  }

}
