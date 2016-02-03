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
namespace Civi\API;

/**
 */
class Api4SelectQuery extends SelectQuery {

  protected $apiVersion = 4;

  /**
   * @inheritDoc
   */
  protected function buildWhereClause() {
    foreach ($this->where as $key => $value) {
      $table_name = NULL;
      $column_name = NULL;

      $field = $this->getField($key);

      if (in_array($key, $this->entityFieldNames)) {
        $table_name = self::MAIN_TABLE_ALIAS;
        $column_name = $key;
      }
      // FIXME: Custom
      elseif (($cf_id = \CRM_Core_BAO_CustomField::getKeyID($key)) != FALSE) {
        //list($table_name, $column_name) = $this->addCustomField($this->apiFieldSpec['custom_' . $cf_id], 'INNER');
      }
      elseif (strpos($key, '.')) {
        $fkInfo = $this->addFkField($key, 'INNER');
        if ($fkInfo) {
          list($table_name, $column_name) = $fkInfo;
          $this->validateNestedInput($key, $value);
        }
      }
      if (!$table_name || !$column_name || is_null($value)) {
        throw new \API_Exception("Invalid field '$key' in where clause.");
      }
      if (!is_array($value)) {
        $this->query->where(array("`$table_name`.`$column_name` = @value"), array(
          "@value" => $value,
        ));
      }
      // We expect only one element in the array, of the form ["operator" => "rhs"].
      elseif (count($value) !== 1) {
        throw new \API_Exception("Invalid value in where clause for field '$key'");
      }
      else {
        $clause = \CRM_Core_DAO::createSQLFilter("`$table_name`.`$column_name`", $value);
        if ($clause === NULL) {
          throw new \API_Exception("Invalid value in where clause for field '$key'");
        }
        $this->query->where($clause);
      }
    }
  }

  /**
   * @inheritDoc
   */
  protected function getFields() {
    $fields = civicrm_api4($this->entity, 'getFields')->indexBy('name');
    return (array) $fields;
  }

  /**
   * Fetch a field from the getFields list
   */
  protected function getField($fieldName) {
    if ($fieldName && isset($this->apiFieldSpec[$fieldName])) {
      return $this->apiFieldSpec[$fieldName];
    }
    return NULL;
  }

}
