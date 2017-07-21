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

namespace Civi\Api4\Query;

use Civi\API\SelectQuery;
use Civi\Api4\Service\Spec\SpecFormatter;
use Civi\Api4\Utils\ArrayInsertionUtil;
use Civi\Api4\Service\Schema\Joinable\CustomGroupJoinable;
use Civi\Api4\Service\Schema\Joinable\Joinable;
use CRM_Core_DAO_AllCoreTables as TableHelper;
use CRM_Core_DAO_CustomField as CustomFieldDAO;

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

  const JOIN_ONE_TO_MANY = 1;
  const JOIN_ONE_TO_ONE = 2;

  /**
   * @var int
   */
  protected $apiVersion = 4;

  /**
   * @var array
   *   Maps select fields to [<table_alias>, <column_alias>]
   */
  protected $fkSelectAliases = array();

  /**
   * @var Joinable[]
   *   The joinable tables that have been joined so far
   */
  protected $joinedTables = array();

  /**
   * @return array|int
   */
  public function run() {
    $this->preRun();
    $baseResults = parent::run();
    return $this->postRun($baseResults);
  }

  /**
   * Gets all FK fields and does the required joins
   */
  protected function preRun() {
    $whereFields = array_column($this->where, 0);
    $allFields = array_merge($whereFields, $this->select, $this->orderBy);
    $dotFields = array_unique(array_filter($allFields, function ($field) {
      return strpos($field, '.') !== false;
    }));

    foreach ($dotFields as $dotField) {
      $this->joinFK($dotField);
    }
  }

  /**
   * @param $primaryResults
   *
   * @return array
   */
  protected function postRun($primaryResults) {
    if (empty($primaryResults)) {
      return $primaryResults;
    }

    $groupedSelects = $this->getJoinedDotSelects();
    foreach ($groupedSelects as $finalAlias => $selects) {
      $path = $this->buildPath($selects[0]);
      $selects = $this->formatSelects($finalAlias, $selects);
      $joinResults = $this->getJoinResults($primaryResults, $selects);

      foreach ($primaryResults as &$primaryResult) {
        $baseId = $primaryResult['id'];
        $filtered = array_filter($joinResults, function ($res) use ($baseId) {
          return ($res['_base_id'] === $baseId);
        });
        $filtered = array_values($filtered);
        ArrayInsertionUtil::insert($primaryResult, $path, $filtered);
      }
    }

    // no associative option
    return array_values($primaryResults);
  }

  /**
   * @inheritDoc
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
    elseif (strpos($key, '.') && isset($this->fkSelectAliases[$key])) {
      list($table_name, $column_name) = explode('.', $this->fkSelectAliases[$key]);
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
    $spec = \Civi::container()->get('spec_gatherer')->getSpec($this->entity, 'get');
    $spec = SpecFormatter::specToArray($spec);

    return $spec['fields'];
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

  /**
   * @param $key
   **/
  protected function joinFK($key) {
    $stack = explode('.', $key);

    if (count($stack) < 2) {
      return;
    }

    $joiner = \Civi::container()->get('joiner');
    $finalDot = strrpos($key, '.');
    $pathString = substr($key, 0, $finalDot);
    $field = substr($key, $finalDot + 1);

    if (!$joiner->canJoin($this, $pathString)) {
      return;
    }

    $joinPath = $joiner->join($this, $pathString);
    $lastLink = end($joinPath);

    // custom groups use aliases for field names
    if ($lastLink instanceof CustomGroupJoinable) {
      $field = CustomFieldDAO::getFieldValue(
        CustomFieldDAO::class,
        $field,
        'column_name',
        'name'
      );
    }

    $this->fkSelectAliases[$key] = sprintf('%s.%s', $lastLink->getAlias(), $field);
  }

  /**
   * @param Joinable $joinable
   *
   * @return $this
   */
  public function addJoinedTable(Joinable $joinable) {
    $this->joinedTables[] = $joinable;

    return $this;
  }

  /**
   * @return FALSE|string
   */
  public function getFrom() {
    return TableHelper::getTableForClass(TableHelper::getFullName($this->entity));
  }

  /**
   * @param string $pathString
   *   Dot separated path to the field, e.g. emails.location_type.label
   *
   * @return array
   *   Index is table alias and value is boolean whether is 1-to-many join
   */
  private function buildPath($pathString) {
    $pathParts = explode('.', $pathString);
    array_pop($pathParts); // remove field
    $path = array();
    $isMultipleChecker = function($alias)  {
      foreach ($this->joinedTables as $table) {
        if ($table->getAlias() === $alias) {
          return $table->getJoinType() === Joinable::JOIN_TYPE_ONE_TO_MANY;
        }
      }
      return false;
    };

    foreach ($pathParts as $part) {
      $path[$part] = $isMultipleChecker($part);
    }

    return $path;
  }

  /**
   * @param $finalAlias
   * @param $selects
   *
   * @return array
   */
  private function formatSelects($finalAlias, $selects) {
    $mainAlias = self::MAIN_TABLE_ALIAS;
    $selectFields = array();

    foreach ($selects as $select) {
      $selectAlias = $this->fkSelectAliases[$select];
      $fieldAlias = substr($select, strrpos($select, '.') + 1);
      $selectFields[$fieldAlias] = $selectAlias;
    }

    $firstSelect = $selects[0];
    $pathParts = explode('.', $firstSelect);
    $numParts = count($pathParts);
    $parentAlias = $numParts > 2 ? $pathParts[$numParts - 3] : $mainAlias;

    $selectFields['id'] = sprintf('%s.id', $finalAlias);
    $selectFields['_parent_id'] = $parentAlias . '.id';
    $selectFields['_base_id'] = $mainAlias . '.id';

    return $selectFields;
  }

  /**
   * @return array
   */
  private function getJoinedDotSelects() {
    $joinedDotSelects = array_filter($this->select, function ($select) {
      return isset($this->fkSelectAliases[$select]);
    });

    $selects = array();
    // group related selects by alias so they can be executed in one query
    foreach ($joinedDotSelects as $select) {
      $parts = explode('.', $select);
      $finalAlias = $parts[count($parts) - 2];
      $selects[$finalAlias][] = $select;
    }

    // sort by depth, e.g. email selects should be done before email.location
    uasort($selects, function ($a, $b) {
      $aFirst = $a[0];
      $bFirst = $b[0];
      return substr_count($aFirst, '.') > substr_count($bFirst, '.');
    });

    return $selects;
  }

  /**
   * @param array $primaryResults
   *   The results from the original query
   * @param $selects
   *   The new values to be selected
   *
   * @return array
   *   The results from the related entity
   */
  protected function getJoinResults($primaryResults, $selects) {
    $baseIds = array_column($primaryResults, 'id');
    $aliasedSelects = array_map(function ($field, $alias) {
      return sprintf('%s as "%s"', $field, $alias);
    }, $selects, array_keys($selects));
    $subQuery = $this->getSubquery($aliasedSelects, $baseIds);

    return $this->runSubquery($subQuery, $selects);
  }

  /**
   * @param \CRM_Utils_SQL_Select $subQuery
   * @param array $originalSelects
   *
   * @return array
   */
  protected function runSubquery($subQuery, $originalSelects) {
    $results = array();
    $subQueryString = str_replace('SELECT', 'SELECT DISTINCT', $subQuery->toSQL());
    $dao = \CRM_Core_DAO::executeQuery($subQueryString);

    while ($dao->fetch()) {
      $current = array();
      foreach ($originalSelects as $alias => $column) {
        // fetch() replaces periods in the select aliases so do the same here
        $convertedName = str_replace('.', '_', $alias);
        if (property_exists($dao, $convertedName)) {
          $current[$alias] = $dao->$convertedName;
        }
      };
      $results[] = $current;
    }

    return $results;
  }

  /**
   * Run a subquery using the original as a base, but replacing the SELECT and
   * WHERE part of the query
   *
   * @param array $replacementSelects
   * @param $baseIds
   *
   * @return \CRM_Utils_SQL_Select
   */
  protected function getSubquery($replacementSelects, $baseIds) {
    $mainAlias = self::MAIN_TABLE_ALIAS;
    $subQuery = QueryCopier::copy($this->query, array(
      'selects' => $replacementSelects,
      'limit' => array(NULL, $this->offset), // there's no limit
      'wheres' => sprintf('%s.id IN (%s)', $mainAlias, implode(',', $baseIds))
    ));

    return $subQuery;
  }
}
