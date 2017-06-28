<?php

namespace Civi\API\Service\Schema;

use Civi\API\Service\Schema\Joinable\Joinable;

class SchemaMap {

  const MAX_JOIN_DEPTH = 3;

  /**
   * @var Table[]
   */
  protected $tables = array();

  /**
   * @param $baseTableName
   * @param $targetTableAlias
   *
   * @return Joinable[]
   *   Array of links to the target table, empty if no path found
   */
  public function getPath($baseTableName, $targetTableAlias) {
    $table = $this->getTableByName($baseTableName);
    $paths = array();

    if (!$table) {
      return $paths;
    }

    $this->findPaths($table, $targetTableAlias, 1, $paths);

    // return the shortest path
    return array_reduce($paths, function ($shortest, array $path) {
      $isShorter = (!$shortest || count($path) < count($shortest));
      return $isShorter ? $path : $shortest;
    });
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
   * Adds a table to the schema map if it has not already been added
   *
   * @param Table $table
   *
   * @return $this
   */
  public function addTable(Table $table) {
    if (!$this->getTableByName($table->getName())) {
      $this->tables[] = $table;
    }

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
   * Recursive function to traverse the schema looking for a path
   *
   * @param Table $table
   *   The current table to base fromm
   * @param string $target
   *   The target joinable table alias
   * @param int $depth
   *   The current level of recursion which reflects the number of joins needed
   * @param Joinable[] $path
   *   (By-reference) The possible paths to the target table
   * @param Joinable[] $currentPath
   *   For internal use only to track the path to reach the target table
   */
  private function findPaths(Table $table, $target, $depth, &$path, $currentPath = array()
  ) {
    static $visited = array();

    // reset visited if new call
    if ($depth === 1) {
      $visited = array();
    }

    $tooFar = $depth > self::MAX_JOIN_DEPTH;
    $beenHere = in_array($table->getName(), $visited);
    $alreadyFound = !empty($path);

    if ($alreadyFound || $tooFar || $beenHere) {
      return;
    }

    // prevent circular reference
    $visited[] = $table->getName();

    foreach ($table->getExternalLinks() as $link) {
      if ($link->getAlias() === $target) {
        $path[] = $currentPath + array($link);
      } else {
        $linkTable = $this->getTableByName($link->getTargetTable());
        if ($linkTable) {
          $nextStep = array_merge($currentPath, [$link]);
          $this->findPaths($linkTable, $target, $depth + 1, $path, $nextStep);
        }
      }
    }
  }
}
