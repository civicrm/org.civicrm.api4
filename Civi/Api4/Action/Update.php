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
 * Update one or more records with new values. Use the where clause to select them.
 *
 * @method $this setValues($values)
 * @method $this addValues($array)
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
   * Field values to set.
   *
   * @var array
   */
  protected $values = [];

  public function _run(Result $result) {
    $bao_name = $this->getBaoName();
    // First run the parent action (get)
    $this->select = ['id'];
    $patch_values = $this->getParams()['values'];
    parent::_run($result);
    // Then act on the result
    $updated_results = [];
    foreach ($result as $item) {
      // todo confirm we need a new object
      $bao = new $bao_name();
      $patch = $item + $patch_values;
      // update it
      $update_result_bao = $bao->create($patch);
      // trim back the junk and just get the array:
      $updated_results[] = $this->baoToArray($update_result_bao);
    }
    $result->exchangeArray($updated_results);
  }

}
