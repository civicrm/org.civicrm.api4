<?php

namespace Civi\API\Service\Schema;

class Table {

  /**
   * @var string
   */
  protected $name;

  /**
   * @var Joinable[]
   *   Array of links to other tables
   */
  protected $tableLinks = array();

  /**
   * @param $name
   * @param string|null $alias
   */
  public function __construct($name, $alias = NULL) {
    $this->name = $name;
    $this->alias = $alias ? $alias : $name;
  }

  /**
   * @return string
   */
  public function getName() {
    return $this->name;
  }

  /**
   * @param string $name
   *
   * @return $this
   */
  public function setName($name) {
    $this->name = $name;

    return $this;
  }

  /**
   * @return Joinable[]
   */
  public function getTableLinks() {
    return $this->tableLinks;
  }

  /**
   * @param string $baseColumn
   * @param Joinable $joinable
   *
   * @return $this
   */
  public function addTableLink($baseColumn, Joinable $joinable) {
    $joinable->setBaseTable($this->getName());
    $joinable->setBaseColumn($baseColumn);
    $this->tableLinks[] = $joinable;

    return $this;
  }

  /**
   * @param mixed $tableLinks
   *
   * @return $this
   */
  public function setTableLinks($tableLinks) {
    $this->tableLinks = $tableLinks;

    return $this;
  }
}
