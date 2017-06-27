<?php

namespace Civi\API\Service\Schema;

class Joinable {
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
   * @param $targetTable
   * @param $targetColumn
   */
  public function __construct($targetTable, $targetColumn) {
    $this->targetTable = $targetTable;
    $this->targetColumn = $targetColumn;
  }

  /**
   * Gets conditions required when joining to a base table
   *
   * @return array
   */
  protected function getConditionsForJoin() {
    $baseCondition = sprintf(
      '%s.%s =  %s.%s',
      $this->baseTable,
      $this->baseColumn,
      $this->getAlias(),
      $this->targetColumn
    );

    return array_merge(array($baseCondition), $this->getConditions());
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
    $defaultAlias = sprintf('%s_to_%s', $this->baseTable, $this->targetTable);

    return $this->alias ?: $defaultAlias;
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
   * @return array
   */
  public function getConditions() {
    return $this->conditions;
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

}
