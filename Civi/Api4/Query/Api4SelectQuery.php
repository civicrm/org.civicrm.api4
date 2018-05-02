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
use Civi\Api4\Entity;
use Civi\Api4\Event\Events;
use Civi\Api4\Event\PostSelectQueryEvent;
use Civi\Api4\Service\Schema\Joinable\CustomGroupJoinable;
use Civi\Api4\Service\Schema\Joinable\Joinable;
use CRM_Core_DAO as DAO;
use CRM_Core_DAO_AllCoreTables as TableHelper;
use CRM_Core_DAO_CustomField as CustomFieldDAO;

/**
 * A query `node` may be in one of three formats:.
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
	 * @var array
	 *            Maps select fields to [<table_alias>, <column_alias>]
	 */
	protected $fkSelectAliases = [];

	/**
	 * @var \Civi\Api4\Service\Schema\Joinable\Joinable[]
	 * The joinable tables that have been joined so far
	 */
	protected $joinedTables = [];

	/**
	 * Why walk when you can.
	 *
	 * @throws \Civi\API\Exception\UnauthorizedException
	 * @throws \API_Exception
	 * @throws \CRM_Core_Exception
	 * @throws \Exception
	 *
	 * @return array|int
	 */
	public function run() {
		$this->preRun();
		$baseResults = $this->getResult();
		$event = new PostSelectQueryEvent($baseResults, $this);
		\Civi::dispatcher()->dispatch(Events::POST_SELECT_QUERY, $event);

		return $event->getResults();
	}

	/**
	 * @throws \Exception
	 * @throws \API_Exception
	 * @throws \CRM_Core_Exception
	 * @throws \Civi\API\Exception\UnauthorizedException
	 *
	 * @return array|int
	 */
	protected function getResult() {
		$this->buildSelectFields();
		$this->buildWhereClause();
		if (\in_array('count_rows', $this->select)) {
			$this->query->select('count(*) as c');
		} else {
			foreach ($this->selectFields as $column => $alias) {
				$this->query->select("${column} as `${alias}`");
			}
			// Order by.
			$this->buildOrderBy();
		}
		// Limit.
		if (!empty($this->limit) || !empty($this->offset)) {
			$this->query->limit($this->limit, $this->offset);
		}
		$result_entities = [];
		$result_dao = DAO::executeQuery($this->query->toSQL());
		while ($result_dao->fetch()) {
			if (\in_array('count_rows', $this->select)) {
				$result_dao->free();

				return (int) $result_dao->c;
			}
			$result_entities[$result_dao->id] = [];
			foreach ($this->selectFields as $column => $alias) {
				$returnName = $alias;
				$alias = str_replace(
					'.',
					'_',
					$alias
				);
				$result_entities[$result_dao->id][$returnName] = $result_dao->{$alias};
				// Backward compatibility on fields names.
				if ($this->isFillUniqueFields
				&& !empty($this->apiFieldSpec[$alias]['uniqueName'])
				) {
					$result_entities[$result_dao->id][$this->apiFieldSpec[$alias]['uniqueName']]
					= $result_dao->{$alias};
				}
				foreach ($this->apiFieldSpec as $returnName => $spec) {
					if (empty($result_entities[$result_dao->id][$returnName])
					&& !empty($result_entities[$result_dao->id][$spec['name']])
					) {
						$result_entities[$result_dao->id][$returnName]
						= $result_entities[$result_dao->id][$spec['name']];
					}
				}
			}
		}
		$result_dao->free();

		return $result_entities;
	}

	/**
	 * Gets all FK fields and does the required joins.
	 *
	 * @throws \Exception
	 */
	protected function preRun() {
		$whereFields = array_column($this->where, 0);
		$allFields = array_merge($whereFields, $this->select, $this->orderBy);
		$dotFields = array_unique(array_filter($allFields, function ($field) {
			return false !== strpos($field, '.');
		}));
		foreach ($dotFields as $dotField) {
			$this->joinFK($dotField);
		}
	}

	/**
	 * @throws \Exception
	 * @throws \API_Exception
	 */
	protected function buildWhereClause() {
		foreach ($this->where as $clause) {
			$sql_clause = $this->treeWalkWhereClause($clause);
			$this->query->where($sql_clause);
		}
	}

	/**
	 * @throws \API_Exception
	 */
	protected function buildOrderBy() {
		foreach ($this->orderBy as $field => $dir) {
			if ('ASC' !== $dir && 'DESC' !== $dir) {
				throw new \API_Exception("Invalid sort direction. Cannot order by ${field} ${dir}");
			}
			if ($this->getField($field)) {
				$this->query->orderBy(self::MAIN_TABLE_ALIAS.'.'.$field." ${dir}");
			} // TODO: Handle joined fields, custom fields, etc.
			else {
				throw new \API_Exception("Invalid sort field. Cannot order by ${field} ${dir}");
			}
		}
	}

	/**
	 * Recursively validate and transform a branch or leaf clause array to SQL.
	 *
	 * @param array $clause
	 *
	 * @throws \API_Exception
	 * @throws \Exception
	 *
	 * @return string SQL where clause
	 *
	 * @uses \validateClauseAndComposeSql() to generate the SQL etc.
	 *
	 * @todo if an 'and' is nested within and 'and' (or or-in-or) then should
	 * flatten that to be a single list of clauses.
	 */
	protected function treeWalkWhereClause($clause) {
		switch ($clause[0]) {
			case 'OR':
			case 'AND':
			// Handle branches.
				if (1 === \count($clause[1])) {
					// A single set so AND|OR is immaterial.
					return $this->treeWalkWhereClause($clause[1][0]);
				}
				$sql_subclauses = [];
				foreach ($clause[1] as $subclause) {
					$sql_subclauses[] = $this->treeWalkWhereClause($subclause);
				}

				return '('.implode("\n".$clause[0], $sql_subclauses).')';
			case 'NOT':
			// Possibly these brackets are redundant.
				return 'NOT ('.$this->treeWalkWhereClause($clause[1]).')';
			default:
				return $this->validateClauseAndComposeSql($clause);
		}
	}

	/**
	 * Validate and transform a leaf clause array to SQL.
	 *
	 * @param array $clause
	 *                      [$fieldName, $operator, $criteria]
	 *
	 * @throws \API_Exception
	 * @throws \Exception
	 *
	 * @return string SQL
	 */
	protected function validateClauseAndComposeSql($clause) {
		list($key, $operator, $criteria) = $clause;
		$value = [$operator => $criteria];
		$table_name = null;
		$column_name = null;
		if (\in_array($key, $this->entityFieldNames)) {
			$table_name = self::MAIN_TABLE_ALIAS;
			$column_name = $key;
		} elseif (strpos($key, '.') && isset($this->fkSelectAliases[$key])) {
			list($table_name, $column_name) = explode(
				'.',
				$this->fkSelectAliases[$key]
			);
		}
		if (!$table_name || !$column_name || null === $value) {
			throw new \API_Exception("Invalid field '${key}' in where clause.");
		}
		$sql_clause = DAO::createSQLFilter("`${table_name}`.`${column_name}`", $value);
		if (null === $sql_clause) {
			throw new \API_Exception("Invalid value in where clause for field '${key}'");
		}

		return $sql_clause;
	}

	/**
	 * @throws \API_Exception
	 *
	 * @return array
	 */
	protected function getFields() {
		$fields = civicrm_api4(
			$this->entity,
			'getFields',
			['action' => 'get', 'includeCustom' => false]
		)->indexBy('name');

		return (array) $fields;
	}

	/**
	 * Fetch a field from the getFields list.
	 *
	 * @param string $fieldName
	 *
	 * @return string|null
	 */
	protected function getField($fieldName) {
		if ($fieldName && isset($this->apiFieldSpec[$fieldName])) {
			return $this->apiFieldSpec[$fieldName];
		}
	}

	/**
	 * @param $key
	 *
	 * @throws \Exception
	 */
	protected function joinFK($key) {
		$stack = explode('.', $key);
		if (\count($stack) < 2) {
			return;
		}
		/** @var \Civi\Api4\Service\Schema\Joiner $joiner */
		$joiner = \Civi::container()->get('joiner');
		$finalDot = strrpos($key, '.');
		$pathString = substr($key, 0, $finalDot);
		$field = substr($key, $finalDot + 1);
		if (!$joiner->canJoin($this, $pathString)) {
			return;
		}
		$joinPath = $joiner->join($this, $pathString);
		$lastLink = end($joinPath);
		// Custom groups use aliases for field names.
		if ($lastLink instanceof CustomGroupJoinable) {
			$field = CustomFieldDAO::getFieldValue(
				CustomFieldDAO::class,
				$field,
				'column_name',
				'name'
			);
		}
		if ('*' === $field) {
			$links = Entity::getLinks()
			->execute()
			->indexBy('entity');
			$new_links = $links->offsetGet($this->entity)['links'];
			$links->exchangeArray($new_links);
			$entityClass = $links->indexBy('alias')->offsetGet($pathString);
			$entity = !empty($entityClass['entity']) ? $entityClass['entity'] : null;
			/** @var \Civi\Api4\Generic\AbstractEntity $entity_fields */
			$entity_fields = "\Civi\Api4\\{$entity}";
			foreach ($entity_fields::getFields()->setAction('get')->execute() as $item) {
				$_key = sprintf(
					'%s.%s',
					$lastLink->getAlias(),
					$item['name']
				);
				$this->fkSelectAliases[$_key] = $_key;
			}

			return;
		}
		$this->fkSelectAliases[$key] = sprintf(
			'%s.%s',
			$lastLink->getAlias(),
			$field
		);
	}

	/**
	 * @param \Civi\Api4\Service\Schema\Joinable\Joinable $joinable
	 *
	 * @return $this
	 */
	public function addJoinedTable(Joinable $joinable) {
		$this->joinedTables[] = $joinable;

		return $this;
	}

	/**
	 * @return false|string
	 */
	public function getFrom() {
		return TableHelper::getTableForClass(TableHelper::getFullName($this->entity));
	}

	/**
	 * @return string
	 */
	public function getEntity() {
		return $this->entity;
	}

	/**
	 * @return array
	 */
	public function getSelect() {
		return $this->select;
	}

	/**
	 * @return array
	 */
	public function getWhere() {
		return $this->where;
	}

	/**
	 * @return array
	 */
	public function getOrderBy() {
		return $this->orderBy;
	}

	/**
	 * @return mixed
	 */
	public function getLimit() {
		return $this->limit;
	}

	/**
	 * @return mixed
	 */
	public function getOffset() {
		return $this->offset;
	}

	/**
	 * @return array
	 */
	public function getSelectFields() {
		return $this->selectFields;
	}

	/**
	 * @return bool
	 */
	public function isFillUniqueFields() {
		return $this->isFillUniqueFields;
	}

	/**
	 * @return \CRM_Utils_SQL_Select
	 */
	public function getQuery() {
		return $this->query;
	}

	/**
	 * @return array
	 */
	public function getJoins() {
		return $this->joins;
	}

	/**
	 * @return array
	 */
	public function getApiFieldSpec() {
		return $this->apiFieldSpec;
	}

	/**
	 * @return array
	 */
	public function getEntityFieldNames() {
		return $this->entityFieldNames;
	}

	/**
	 * @return array
	 */
	public function getAclFields() {
		return $this->aclFields;
	}

	/**
	 * @return bool|string
	 */
	public function getCheckPermissions() {
		return $this->checkPermissions;
	}

	/**
	 * @return int
	 */
	public function getApiVersion() {
		return $this->apiVersion;
	}

	/**
	 * @return array
	 */
	public function getFkSelectAliases() {
		return $this->fkSelectAliases;
	}

	/**
	 * @return \Civi\Api4\Service\Schema\Joinable\Joinable[]
	 */
	public function getJoinedTables() {
		return $this->joinedTables;
	}
}
