<?php

namespace Civi\API\Service\Schema\Joinable;

class Joinable {

  const JOIN_SIDE_LEFT = 'LEFT';
  const JOIN_SIDE_INNER = 'INNER';

  const JOIN_TYPE_ONE_TO_ONE = 1;
  const JOIN_TYPE_MANY_TO_ONE = 2;
  const JOIN_TYPE_ONE_TO_MANY = 3;

  /**
   * @var string
   */
  protected $baseTable;

  /**
   * @var string
   */
  protected $baseColumn;

  /**
   * @var string
   */
  protected $targetTable;

  /**
   * @var string
   */
  protected $targetColumn;

  /**
   * @var string
   */
  protected $alias;

  /**
   * @var array
   */
  protected $conditions = array();

  /**
   * @var string
   */
  protected $joinSide = self::JOIN_SIDE_INNER;

  /**
   * @var int
   */
  protected $joinType = self::JOIN_TYPE_ONE_TO_ONE;

  /**
   * @param $targetTable
   * @param $targetColumn
   * @param string|null $alias
   */
  public function __construct($targetTable, $targetColumn, $alias = NULL) {
    $this->targetTable = $targetTable;
    $this->targetColumn = $targetColumn;
    $this->alias = $alias ?: str_replace('civicrm_', '', $targetTable);
  }

  /**
   * Gets conditions required when joining to a base table
   *
   * @param string|null $baseTableAlias
   *   Name of the base table, if aliased.
   *
   * @return array
   */
  public function getConditionsForJoin($baseTableAlias = NULL) {
    $baseCondition = sprintf(
      '%s.%s =  %s.%s',
      $baseTableAlias ?: $this->baseTable,
      $this->baseColumn,
      $this->getAlias(),
      $this->targetColumn
    );

    return array_merge(array($baseCondition), $this->conditions);
  }

  /**
   * @return string
   */
  public function getBaseTable() {
    return $this->baseTable;
  }

  /**
   * @param string $baseTable
   *
   * @return $this
   */
  public function setBaseTable($baseTable) {
    $this->baseTable = $baseTable;

    return $this;
  }

  /**
   * @return string
   */
  public function getBaseColumn() {
    return $this->baseColumn;
  }

  /**
   * @param string $baseColumn
   *
   * @return $this
   */
  public function setBaseColumn($baseColumn) {
    $this->baseColumn = $baseColumn;

    return $this;
  }

  /**
   * @return string
   */
  public function getAlias() {
    return $this->alias;
  }

  /**
   * @param string $alias
   *
   * @return $this
   */
  public function setAlias($alias) {
    $this->alias = $alias;

    return $this;
  }

  /**
   * @return string
   */
  public function getTargetTable() {
    return $this->targetTable;
  }

  /**
   * @return string
   */
  public function getTargetColumn() {
    return $this->targetColumn;
  }

  /**
   * @param $condition
   *
   * @return $this
   */
  public function addCondition($condition) {
    $this->conditions[] = $condition;

    return $this;
  }
  /**
   * @param array $conditions
   *
   * @return $this
   */
  public function setConditions($conditions) {
    $this->conditions = $conditions;

    return $this;
  }

  /**
   * @return string
   */
  public function getJoinSide() {
    return $this->joinSide;
  }

  /**
   * @param string $joinSide
   *
   * @return $this
   */
  public function setJoinSide($joinSide) {
    $this->joinSide = $joinSide;

    return $this;
  }

  /**
   * @return int
   */
  public function getJoinType() {
    return $this->joinType;
  }

  /**
   * @param int $joinType
   *
   * @return $this
   */
  public function setJoinType($joinType) {
    $this->joinType = $joinType;

    return $this;
  }
}
