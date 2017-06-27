<?php

namespace Civi\API\Service\Schema;

class SchemaMap {

  const MAX_JOIN_DEPTH = 2;

  /**
   * @var Table[]
   */
  protected $tables = array();

  /**
   * @param $baseTableName
   * @param $targetTableName
   *
   * @return Joinable[]
   *   Array of links to the target table, empty if no path found
   */
  public function getPath($baseTableName, $targetTableName) {
    $table = $this->getTableByName($baseTableName);
    $path = array();

    if (!$table) {
      return $path;
    }

    $this->findInMap($table, $targetTableName, 1, $path);

    return $path;
  }

  /**
   * @return Table[]
   */
  public function getTables() {
    return $this->tables;
  }

  /**
   * @param $name
   *
   * @return Table|null
   */
  public function getTableByName($name) {
    foreach ($this->tables as $table) {
      if ($table->getName() === $name) {
        return $table;
      }
    }

    return NULL;
  }

  /**
   * @param Table $table
   *
   * @return $this
   */
  public function addTable(Table $table) {
    $this->tables[] = $table;

    return $this;
  }

  /**
   * @param array $tables
   */
  public function addTables(array $tables) {
    foreach ($tables as $table) {
      $this->addTable($table);
    }
  }

  /**
   * @param Table $table
   *   The current table to base fromm
   * @param string $target
   *   The target table name
   * @param int $depth
   *   The current level of recursion which reflects the number of joins needed
   * @param Joinable[] $path
   *   (By-reference) The path to the target table
   * @param Joinable[] $currentPath
   *   For internal use only to track the path to reach the target table
   */
  private function findInMap(Table $table, $target, $depth, &$path, $currentPath = array()
  ) {
    static $visited = array();

    // reset visited if new call
    if ($depth === 1) {
      $visited = array();
    }

    $tooFar = $depth > self::MAX_JOIN_DEPTH;
    $beenHere = in_array($table->getName(), $visited);
    if ($tooFar || $beenHere) {
      return;
    }

    // prevent circular reference
    $visited[] = $table->getName();

    foreach ($table->getTableLinks() as $link) {
      $currentPath[] = $link;
      if ($link->getTargetTable() === $target) {
        $path = $currentPath;
      } else {
        $linkTable = $this->getTableByName($link->getTargetTable());
        $this->findInMap($linkTable, $target, ++$depth, $path, $currentPath);
      }
    }
  }
}
