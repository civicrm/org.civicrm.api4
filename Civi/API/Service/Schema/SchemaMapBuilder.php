<?php

namespace Civi\API\Service\Schema;

use Civi\API\Event\Events;
use Civi\API\Event\SchemaMapBuildEvent;
use Civi\API\Service\Schema\Joinable\Joinable;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Civi\API\Service\Schema\Joinable\OptionValueJoinable;
use CRM_Core_DAO_AllCoreTables as TableHelper;
use CRM_Utils_Array as ArrayHelper;

class SchemaMapBuilder {
  /**
   * @var EventDispatcherInterface
   */
  protected $dispatcher;

  /**
   * @param EventDispatcherInterface $dispatcher
   */
  public function __construct(EventDispatcherInterface $dispatcher) {
    $this->dispatcher = $dispatcher;
  }

  /**
   * @return SchemaMap
   */
  public function build() {
    $map = new SchemaMap();
    $this->loadTables($map);

    $event = new SchemaMapBuildEvent($map);
    $this->dispatcher->dispatch(Events::SCHEMA_MAP_BUILD, $event);

    return $map;
  }

  /**
   * Add all tables and joins
   *
   * @param SchemaMap $map
   */
  private function loadTables(SchemaMap $map) {
    /** @var \CRM_Core_DAO $daoName */
    foreach (TableHelper::get() as $daoName => $data) {
      $table = new Table($data['table']);
      foreach ($daoName::fields() as $field => $fieldData) {
        $this->addJoins($table, $field, $fieldData);
      }
      $map->addTable($table);
    }

    // add back references
    foreach ($map->getTables() as $table) {
      foreach ($table->getTableLinks() as $link) {

        // there are too many possible joins from option value so skip
        if ($link instanceof OptionValueJoinable) {
          continue;
        }

        $target = $map->getTableByName($link->getTargetTable());
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
    $keyColumn = ArrayHelper::value('keyColumn', $data, 'id');

    if ($tableName) {
      $alias = str_replace('civicrm_', '', $tableName);
      $joinable = new Joinable($tableName, $keyColumn, $alias);
      $condition = ArrayHelper::value('condition', $data);
      if ($condition) {
        $joinable->addCondition($condition);
      }
      $table->addTableLink($field, $joinable);
    } elseif ($optionGroupName) {
      $joinable = new OptionValueJoinable($optionGroupName, $keyColumn);
      $table->addTableLink($field, $joinable);
    }
  }
}
