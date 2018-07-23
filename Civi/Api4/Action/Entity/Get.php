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

namespace Civi\Api4\Action\Entity;

use Civi\Api4\Generic\AbstractAction;
use Civi\Api4\Generic\Result;
use Civi\Api4\Utils\ReflectionUtils;

/**
 * Get entities
 *
 * @method $this setSelect(array $value)
 * @method $this addSelect(string $value)
 * @method array getSelect()
 */
class Get extends AbstractAction {

  /**
   * Which attributes of the entities should be returned?
   *
   * @options name, description, comment
   *
   * @var array
   */
  protected $select = [];

  /**
   * Scan all api directories to discover entities
   *
   * @param Result $result
   */
  public function _run(Result $result) {
    $entities = [];
    foreach (explode(PATH_SEPARATOR, get_include_path()) as $path) {
      $dir = \CRM_Utils_File::addTrailingSlash($path) . 'Civi/Api4';
      if (is_dir($dir)) {
        foreach (glob("$dir/*.php") as $file) {
          $matches = [];
          preg_match('/(\w*).php/', $file, $matches);
          $entity = ['name' => $matches[1]];
          if (!$this->select || $this->select != ['name']) {
            $this->addDocs($entity);
          }
          if ($this->select) {
            $entity = array_intersect_key($entity, array_flip($this->select));
          }
          $entities[$matches[1]] = $entity;
        }
      }
    }
    ksort($entities);
    $result->exchangeArray(array_values($entities));
  }

  private function addDocs(&$entity) {
    $reflection = new \ReflectionClass("\\Civi\\Api4\\" . $entity['name']);
    $entity += ReflectionUtils::getCodeDocs($reflection);
    unset($entity['package'], $entity['method']);
  }

}
