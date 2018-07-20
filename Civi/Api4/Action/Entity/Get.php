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

use Civi\Api4\CustomGroup;
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
          $entities[$matches[1]] = $entity;
        }
      }
    }
    unset($entities['CustomValue']);
    // Add custom-field pseudo-entities
    $customEntities = CustomGroup::get()
      ->addWhere('is_multiple', '=', 1)
      ->addWhere('is_active', '=', 1)
      ->setSelect(['name', 'title', 'help_pre', 'help_post'])
      ->setCheckPermissions(FALSE)
      ->execute();
    foreach ($customEntities as $customEntity) {
      $fieldName = 'Custom_' . $customEntity['name'];
      $entities[$fieldName] = [
        'name' => $fieldName,
        'description' => $customEntity['title'],
      ];
      if (!empty($customEntity['help_pre'])) {
        $entities[$fieldName]['comment'] = $customEntity['help_pre'];
      }
      if (!empty($customEntity['help_post'])) {
        $pre = empty($customEntity['help_pre']) ? '' : $customEntity['help_pre'] . "\n\n";
        $entities[$fieldName]['comment'] = $pre . $customEntity['help_post'];
      }
    }
    ksort($entities);
    if ($this->select) {
      foreach ($entities as &$entity) {
        $entity = array_intersect_key($entity, array_flip($this->select));
      }
    }
    $result->exchangeArray(array_values($entities));
  }

  private function addDocs(&$entity) {
    $reflection = new \ReflectionClass("\\Civi\\Api4\\" . $entity['name']);
    $entity += ReflectionUtils::getCodeDocs($reflection);
    unset($entity['package'], $entity['method']);
  }

}
