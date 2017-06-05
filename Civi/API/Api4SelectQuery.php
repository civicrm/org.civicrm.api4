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
      $fkInfo = $this->addFkField($key, 'INNER');
      if ($fkInfo) {
        list($table_name, $column_name) = $fkInfo;
        $this->validateNestedInput($key, $value);
      } else {
        $customField = $this->addDotNotationCustomField($key);
        if ($customField) {
          $table_name = $customField[0];
          $column_name = $customField[1];
        }
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

      $parts = explode('.', $selectAlias);
      switch (count($parts)) {
        case 2:
          $customFieldData = $this->addDotNotationCustomField($selectAlias);
          break;
        case 3:
          // todo optimize
          $customFieldData = $this->addDotNotationCustomFieldWithOptionValue($selectAlias);
          break;
        default:
          throw new \Exception('Invalid dot notation in select');
      }

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

    $optionValueField = ArrayHelper::value(2, $parts);
    $addedField = $this->addDotNotationCustomField($field);
    $customValueAlias = $addedField[0];
    $customValueColumn = $addedField[1];

    $optionGroupID = \CRM_Core_BAO_CustomField::getFieldValue(
      \CRM_Core_DAO_CustomField::class,
      $fieldName,
      'option_group_id',
      'name'
    );

    if (NULL === $optionGroupID) {
      throw new \API_Exception(
        'Cannot select option value field for a custom field that does not'
        . ' have an option group defined'
      );
    }

    $optionValueAlias = sprintf('%s_to_%s', self::MAIN_TABLE_ALIAS, 'option_value');
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
      'INNER',
      'civicrm_option_value',
      $optionValueAlias,
      array($optionValueMatching, $optionGroupRestriction)
    );

    return array($optionValueAlias, $optionValueField);
  }

}
