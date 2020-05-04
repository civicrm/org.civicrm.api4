<?php

namespace Civi\Api4\Service\Schema;

use Civi\Api4\Entity;
use Civi\Api4\Event\Events;
use Civi\Api4\Event\SchemaMapBuildEvent;
use Civi\Api4\Service\Schema\Joinable\CustomGroupJoinable;
use Civi\Api4\Service\Schema\Joinable\Joinable;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Civi\Api4\Service\Schema\Joinable\OptionValueJoinable;
use Civi\Api4\Utils\CoreUtil;
use CRM_Core_DAO_AllCoreTables as AllCoreTables;
use CRM_Utils_Array as UtilsArray;

class SchemaMapBuilder {

  /**
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $dispatcher;

  /**
   * @var array
   */
  protected $apiEntities;

  /**
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
   */
  public function __construct(EventDispatcherInterface $dispatcher) {
    $this->dispatcher = $dispatcher;
    $this->apiEntities = array_keys((array) Entity::get()->setCheckPermissions(FALSE)->addSelect('name')->execute()->indexBy('name'));
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
    foreach (AllCoreTables::get() as $daoName => $data) {
      $table = new Table($data['table']);
      foreach ($daoName::fields() as $field => $fieldData) {
        $this->addJoins($table, $field, $fieldData);
      }
      $map->addTable($table);
      if (in_array($data['name'], $this->apiEntities)) {
        $this->addCustomFields($map, $table, $data['name']);
      }
    }

    $this->addBackReferences($map);
  }

  /**
   * @param Table $table
   * @param string $field
   * @param array $data
   */
  private function addJoins(Table $table, $field, array $data) {
    $fkClass = UtilsArray::value('FKClassName', $data);

    // can there be multiple methods e.g. pseudoconstant and fkclass
    if ($fkClass) {
      $tableName = AllCoreTables::getTableForClass($fkClass);
      $fkKey = UtilsArray::value('FKKeyColumn', $data, 'id');
      $alias = str_replace('_id', '', $field);
      $joinable = new Joinable($tableName, $fkKey, $alias);
      $joinable->setJoinType($joinable::JOIN_TYPE_MANY_TO_ONE);
      $table->addTableLink($field, $joinable);
    }
    elseif (UtilsArray::value('pseudoconstant', $data)) {
      $this->addPseudoConstantJoin($table, $field, $data);
    }
  }

  /**
   * @param Table $table
   * @param string $field
   * @param array $data
   */
  private function addPseudoConstantJoin(Table $table, $field, array $data) {
    $pseudoConstant = UtilsArray::value('pseudoconstant', $data);
    $tableName = UtilsArray::value('table', $pseudoConstant);
    $optionGroupName = UtilsArray::value('optionGroupName', $pseudoConstant);
    $keyColumn = UtilsArray::value('keyColumn', $pseudoConstant, 'id');

    if ($tableName) {
      $alias = str_replace('civicrm_', '', $tableName);
      $joinable = new Joinable($tableName, $keyColumn, $alias);
      $condition = UtilsArray::value('condition', $pseudoConstant);
      if ($condition) {
        $joinable->addCondition($condition);
      }
      $table->addTableLink($field, $joinable);
    }
    elseif ($optionGroupName) {
      $keyColumn = UtilsArray::value('keyColumn', $pseudoConstant, 'value');
      $joinable = new OptionValueJoinable($optionGroupName, NULL, $keyColumn);

      if (!empty($data['serialize'])) {
        $joinable->setJoinType($joinable::JOIN_TYPE_ONE_TO_MANY);
      }

      $table->addTableLink($field, $joinable);
    }
  }

  /**
   * Loop through existing links and provide link from the other side
   *
   * @param SchemaMap $map
   */
  private function addBackReferences(SchemaMap $map) {
    foreach ($map->getTables() as $table) {
      foreach ($table->getTableLinks() as $link) {
        // there are too many possible joins from option value so skip
        if ($link instanceof OptionValueJoinable) {
          continue;
        }

        $target = $map->getTableByName($link->getTargetTable());
        $tableName = $link->getBaseTable();
        $plural = str_replace('civicrm_', '', $this->getPlural($tableName));
        $joinable = new Joinable($tableName, $link->getBaseColumn(), $plural);
        $joinable->setJoinType($joinable::JOIN_TYPE_ONE_TO_MANY);
        $target->addTableLink($link->getTargetColumn(), $joinable);
      }
    }
  }

  /**
   * Simple implementation of pluralization.
   * Could be replaced with symfony/inflector
   *
   * @param string $singular
   *
   * @return string
   */
  private function getPlural($singular) {
    $last_letter = substr($singular, -1);
    switch ($last_letter) {
      case 'y':
        return substr($singular, 0, -1) . 'ies';

      case 's':
        return $singular . 'es';

      default:
        return $singular . 's';
    }
  }

  /**
   * @param \Civi\Api4\Service\Schema\SchemaMap $map
   * @param \Civi\Api4\Service\Schema\Table $baseTable
   * @param string $entity
   */
  private function addCustomFields(SchemaMap $map, Table $baseTable, $entity) {
    $links = CoreUtil::getCustomTableLinks($entity);

    foreach ($links as $alias => $link) {
      $customTable = $map->getTableByName($link['tableName']);
      if (!$customTable) {
        $customTable = new Table($link['tableName']);
      }

      if (!empty($link['option_group_id'])) {
        $optionValueJoinable = new OptionValueJoinable($link['option_group_id'], $link['label']);
        foreach ($link['columns'] as $columnName) {
          $customTable->addTableLink($columnName, $optionValueJoinable);
        }
      }

      $map->addTable($customTable);

      $joinable = new CustomGroupJoinable($link['tableName'], $alias, $link['isMultiple'], $entity, $link['columns']);
      $baseTable->addTableLink('id', $joinable);
    }
  }

}
