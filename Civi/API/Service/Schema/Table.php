<?php

namespace Civi\API\Service\Schema;

use Civi\API\Service\Schema\Joinable\Joinable;

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
   */
  public function __construct($name) {
    $this->name = $name;
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
   * @return Joinable[]
   *   Only those links that are not joining the table to itself
   */
  public function getExternalLinks() {
    return array_filter($this->tableLinks, function (Joinable $joinable) {
      return $joinable->getTargetTable() !== $this->getName();
    });
  }

  /**
   * @param string $baseColumn
   * @param Joinable $joinable
   *
   * @return $this
   */
  public function addTableLink($baseColumn, Joinable $joinable) {
    $target= $joinable->getTargetTable();
    $targetCol = $joinable->getTargetColumn();
    $alias = $joinable->getAlias();

    if (!$this->hasLink($target, $targetCol, $alias)) {
      $joinable->setBaseTable($this->getName());
      $joinable->setBaseColumn($baseColumn);
      $this->tableLinks[] = $joinable;
    }

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

  /**
   * @param $target
   * @param $targetCol
   * @param $alias
   *
   * @return bool
   */
  private function hasLink($target, $targetCol, $alias) {
    foreach ($this->tableLinks as $link) {
      if ($link->getTargetTable() === $target
        && $link->getTargetColumn() === $targetCol
        && $link->getAlias() === $alias
      ) {
        return TRUE;
      }
    }

    return FALSE;
  }
}
