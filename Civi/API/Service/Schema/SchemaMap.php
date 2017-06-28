<?php

namespace Civi\API\Service\Schema;

use Civi\API\Service\Schema\Joinable\Joinable;
use Civi\API\Service\Schema\Joinable\OptionValueJoinable;
use CRM_Core_DAO_AllCoreTables as TableHelper;
use CRM_Utils_Array as ArrayHelper;

class SchemaMap {

  const MAX_JOIN_DEPTH = 3;

  /**
   * @var Table[]
   */
  protected $tables = array();

  /**
   * @param bool $autoload
   *   Whether to autoload the tables using core classes. If not tables must
   *   be added manually
   */
  public function __construct($autoload = true) {
    if ($autoload) {
      $this->loadTables();
    }
  }

  /**
   * Add all tables and joins
   */
  private function loadTables() {
    /** @var \CRM_Core_DAO $daoName */
    foreach (TableHelper::get() as $daoName => $data) {
      $table = new Table($data['table']);
      foreach ($daoName::fields() as $field => $fieldData) {
        $this->addJoins($table, $field, $fieldData);
      }
      $this->addTable($table);
    }

    // add back references
    foreach ($this->tables as $table) {
      foreach ($table->getTableLinks() as $link) {

        // there are too many possible joins from option value so skip
        if ($link instanceof OptionValueJoinable) {
          continue;
        }

        $target = $this->getTableByName($link->getTargetTable());
        $joinable = new Joinable($link->getBaseTable(), $link->getBaseColumn());
        $target->addTableLink($link->getTargetColumn(), $joinable);
      }
    }
  }

  /**
   * @param Table $table
   * @param string $field
   * @param array $data
   */
  private function addJoins(Table $table, $field, array $data) {
    $pseudoConstant = ArrayHelper::value('pseudoconstant', $data);
    $fkClass = ArrayHelper::value('FKClassName', $data);

    // can there be multiple methods e.g. pseudoconstant and fkclass
    if ($fkClass) {
      $tableName = TableHelper::getTableForClass($fkClass);
      $fkKey = ArrayHelper::value('FKKeyColumn', $data, 'id');
      $table->addTableLink($field, new Joinable($tableName, $fkKey));
    } else if ($pseudoConstant) {
      $this->addPseudoConstantJoin($table, $field, $pseudoConstant);
    }
  }

  /**
   * @param Table $table
   * @param string $field
   * @param array $data
   */
  private function addPseudoConstantJoin(Table $table, $field, array $data) {
    $tableName = ArrayHelper::value('table', $data);
    $optionGroupName = ArrayHelper::value('optionGroupName', $data);

    if ($tableName) {
      $keyColumn = ArrayHelper::value('keyColumn', $data, 'id');
      $alias = str_replace('civicrm_', '', $tableName);
      $joinable = new Joinable($tableName, $keyColumn, $alias);
      $condition = ArrayHelper::value('condition', $data);
      if ($condition) {
        $joinable->addCondition($condition);
      }
      $table->addTableLink($field, $joinable);
    } elseif ($optionGroupName) {
      $joinable = new OptionValueJoinable($optionGroupName);
      $table->addTableLink($field, $joinable);
    }
  }

  /**
   * @param $baseTableName
   * @param $targetTableAlias
   *
   * @return Joinable[]
   *   Array of links to the target table, empty if no path found
   */
  public function getPath($baseTableName, $targetTableAlias) {
    $table = $this->getTableByName($baseTableName);
    $path = array();

    if (!$table) {
      return $path;
    }

    $this->findInMap($table, $targetTableAlias, 1, $path);

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
  private function findInMap(Table $table, $target, $depth, &$path, $currentPath = array()
  ) {
    static $visited = array();

    // reset visited if new call
    if ($depth === 1) {
      $visited = array();
    }

    $depth++;
    $tooFar = $depth > self::MAX_JOIN_DEPTH;
    $beenHere = in_array($table->getName(), $visited);
    $alreadyFound = !empty($path);

    if ($alreadyFound || $tooFar || $beenHere) {
      return;
    }

    // prevent circular reference
    $visited[] = $table->getName();

    foreach ($table->getExternalLinks() as $link) {
      if (empty($path) && $link->getAlias() === $target) {
        $path = $currentPath + array($link);
      } else {
        $linkTable = $this->getTableByName($link->getTargetTable());
        if ($linkTable) {
          $nextStep = array_merge($currentPath, [$link]);
          $this->findInMap($linkTable, $target, $depth, $path, $nextStep);
        }
      }
    }
  }
}
