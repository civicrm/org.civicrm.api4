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
 * Delete one or more items, based on criteria specified in Where param.
 */
class Delete extends Get {

  /**
   * Criteria for selecting items to delete.
   *
   * @required
   * @var array
   */
  protected $where = [];

  /**
   * Field by which objects are identified.
   *
   * @var string
   */
  private $idField = 'id';

  /**
   * Batch delete function
   */
  public function _run(Result $result) {
    $this->setSelect([$this->idField]);
    $defaults = $this->getParamDefaults();
    if ($defaults['where'] && !array_diff_key($this->where, $defaults['where'])) {
      throw new \API_Exception('Cannot delete with no "where" paramater specified');
    }

    $items = $this->getObjects();

    $ids = $this->deleteObjects($items);

    $result->exchangeArray($ids);
  }

  /**
   * @param $items
   * @return array
   * @throws \API_Exception
   */
  protected function deleteObjects($items) {
    $ids = [];
    $baoName = $this->getBaoName();
    if (method_exists($baoName, 'del')) {
      foreach ($items as $item) {
        $args = [$item[$this->idField]];
        $bao = call_user_func_array([$baoName, 'del'], $args);
        if ($bao !== FALSE) {
          $ids[] = $item[$this->idField];
        }
        else {
          throw new \API_Exception("Could not delete {$this->getEntity()} id {$item[$this->idField]}");
        }
      }
    }
    else {
      foreach ($items as $item) {
        $bao = new $baoName();
        $bao->id = $item[$this->idField];
        // delete it
        $action_result = $bao->delete();
        if ($action_result) {
          $ids[] = $item[$this->idField];
        }
        else {
          throw new \API_Exception("Could not delete {$this->getEntity()} id {$item[$this->idField]}");
        }
      }
    }
    return $ids;
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

  /**
   * @inheritDoc
   */
  public function getParamInfo($param = NULL) {
    $info = parent::getParamInfo($param);
    if (!$param) {
      // Delete doesn't actually let you select fields.
      unset($info['select']);
    }
    return $info;
  }

}
