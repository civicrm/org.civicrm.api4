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

use CRM_Utils_Array as ArrayHelper;
use CRM_Core_DAO_AllCoreTables as TableHelper;

/**
 * A query `node` may be in one of three formats:
 *
 * * leaf: [$fieldName, $operator, $criteria]
 * * negated: ['NOT', $node]
 * * branch: ['OR|NOT', [$node, $node, ...]]
 *
 * Leaf operators are one of:
 *
 * * '=', '<=', '>=', '>', '<', 'LIKE', "<>", "!=",
 * * "NOT LIKE", 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN',
 * * 'IS NOT NULL', or 'IS NULL'.
 */
class Api4SelectQuery extends SelectQuery {

  /**
   * @var int
   */
  protected $apiVersion = 4;

  /**
   * @inheritDoc
   * new style = [$fieldName, $operator, $criteria]
   */
  protected function buildWhereClause() {
    foreach ($this->where as $clause) {
      $sql_clause = $this->treeWalkWhereClause($clause);
      $this->query->where($sql_clause);
    }
  }

  /**
   * Recursively validate and transform a branch or leaf clause array to SQL.
   *
   * @param array $clause
   * @return string SQL where clause
   *
   * @uses validateClauseAndComposeSql() to generate the SQL etc.
   * @todo if an 'and' is nested within and 'and' (or or-in-or) then should
   * flatten that to be a single list of clauses.
   */
  protected function treeWalkWhereClause($clause) {
    switch ($clause[0]) {
    case 'OR':
    case 'AND':
      // handle branches
      if (count($clause[1]) === 1) {
        // a single set so AND|OR is immaterial
        return $this->treeWalkWhereClause($clause[1][0]);
      }
      else {
        $sql_subclauses = [];
        foreach ($clause[1] as $subclause) {
          $sql_subclauses[] = $this->treeWalkWhereClause($subclause);
        }
        return '(' . implode("\n" . $clause[0], $sql_subclauses) . ')';
      }
    case 'NOT':
      // possibly these brackets are redundant
      return 'NOT ('
        . $this->treeWalkWhereClause($clause[1]) . ')';
      break;
    default:
      return $this->validateClauseAndComposeSql($clause);
    }
  }

  /**
   * Validate and transform a leaf clause array to SQL.
   * @param array $clause [$fieldName, $operator, $criteria]
   * @return string SQL
   */
  protected function validateClauseAndComposeSql($clause) {
    list($key, $operator, $criteria) = $clause;
    $value = array($operator => $criteria);
    // $field = $this->getField($key); // <<-- unused
    // derive table and column:
    $table_name = NULL;
    $column_name = NULL;
    if (in_array($key, $this->entityFieldNames)) {
      $table_name = self::MAIN_TABLE_ALIAS;
      $column_name = $key;
    }
    elseif (strpos($key, '.')) {
      $fkInfo =
        $this->addDotNotationCustomField($key) ?:
        $this->joinFK($key, 'LEFT');

      if ($fkInfo) {
        $table_name = $fkInfo[0];
        $column_name = $fkInfo[1];
      }
    }

    if (!$table_name || !$column_name || is_null($value)) {
      throw new \API_Exception("Invalid field '$key' in where clause.");
    }

    $sql_clause = \CRM_Core_DAO::createSQLFilter("`$table_name`.`$column_name`", $value);
    if ($sql_clause === NULL) {
      throw new \API_Exception("Invalid value in where clause for field '$key'");
    }
    return $sql_clause;
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
   *
   * @param string $fieldName
   *
   * @return string|null
   */
  protected function getField($fieldName) {
    if ($fieldName && isset($this->apiFieldSpec[$fieldName])) {
      return $this->apiFieldSpec[$fieldName];
    }
    return NULL;
  }

  protected function buildSelectFields() {
    parent::buildSelectFields();

    foreach ($this->select as $selectAlias) {
      $alreadyAdded = in_array($selectAlias, $this->selectFields);
      $containsDot = strpos($selectAlias, '.') !== FALSE;
      if ($alreadyAdded || !$containsDot) {
        continue;
      }

      $customFieldData = $this->addDotNotationCustomField($selectAlias);

      if (!$customFieldData) {
        continue;
      }

      $tableAlias = $customFieldData[0];
      $columnName = $customFieldData[1];
      $field = sprintf('%s.%s', $tableAlias, $columnName);

      $this->selectFields[$field] = $selectAlias;
    }
  }

  /**
   * @param $customField
   *   The field name in the format CustomGroupName.CustomFieldName
   *
   * @return array|null
   *   An array containing the added table alias and column name
   */
  protected function addDotNotationCustomField($customField) {
    $parts = explode('.', $customField);

    if (count($parts) === 3) {
      return $this->addDotNotationCustomFieldWithOptionValue($customField);
    } elseif (count($parts) !== 2) {
      throw new \Exception('Invalid dot notation in select');
    }

    $groupName = ArrayHelper::value(0, $parts);
    $fieldName = ArrayHelper::value(1, $parts);

    $tableName = \CRM_Core_BAO_CustomGroup::getFieldValue(
      \CRM_Core_DAO_CustomGroup::class,
      $groupName,
      'table_name',
      'name'
    );
    $columnName = \CRM_Core_BAO_CustomField::getFieldValue(
      \CRM_Core_DAO_CustomField::class,
      $fieldName,
      'column_name',
      'name'
    );

    if (!$tableName || !$columnName) {
      return NULL;
    }

    return $this->addCustomField(
      array('table_name' => $tableName, 'column_name' => $columnName),
      'INNER'
    );
  }

  /**
   * @param $field
   *
   * @return array
   *   An array containing the option value table alias and column name
   */
  protected function addDotNotationCustomFieldWithOptionValue($field) {
    $parts = explode('.', $field);
    $fieldName = ArrayHelper::value(1, $parts);
    $groupName = ArrayHelper::value(0, $parts);
    $optionValueField = ArrayHelper::value(2, $parts);
    $customGroupAndField = sprintf('%s.%s', $groupName, $fieldName);

    $addedField = $this->addDotNotationCustomField($customGroupAndField);
    $customValueAlias = $addedField[0];
    $customValueColumn = $addedField[1];

    $optionGroupID = \CRM_Core_BAO_CustomField::getFieldValue(
      \CRM_Core_DAO_CustomField::class,
      $fieldName,
      'option_group_id',
      'name'
    );

    // Cannot select a third level value if option group doesn't exist
    if (NULL === $optionGroupID) {
      return array();
    }

    $optionValueAlias = sprintf(
      '%s_to_%s_options',
      self::MAIN_TABLE_ALIAS,
      $customValueColumn
    );
    $optionValueMatching = sprintf(
      '`%s`.value = `%s`.`%s`',
      $optionValueAlias,
      $customValueAlias,
      $customValueColumn
    );
    $optionGroupRestriction = sprintf(
      '`%s`.option_group_id =  %d',
      $optionValueAlias,
      $optionGroupID
    );

    $this->join(
      'LEFT',
      'civicrm_option_value',
      $optionValueAlias,
      array($optionValueMatching, $optionGroupRestriction)
    );

    return array($optionValueAlias, $optionValueField);
  }

  /**
   * @param $key
   * @param $side
   *
   * @return array
   */
  protected function joinFK($key, $side) {
    // check if it's a custom field first
    $customFieldData = $this->addDotNotationCustomField($key);
    if ($customFieldData) {
      return $customFieldData;
    }

    $stack = explode('.', $key);
    $fieldData = array();
    if (count($stack) < 2) {
      return $fieldData;
    }

    $joiner = \Civi::container()->get('joiner');
    $tableAlias = NULL;

    // todo check if can join before joining
    while (count($stack) > 1) {
      $tableAlias = array_shift($stack);
      $joiner->join($this, $tableAlias, $side);
    }

    $field = array_shift($stack);

    return array($tableAlias, $field);
  }

  /**
   * @return FALSE|string
   */
  public function getFrom() {
    return TableHelper::getTableForClass(TableHelper::getFullName($this->entity));
  }

}
