<?php

namespace Civi\Api4\Service\Schema;

use Civi\Api4\Event\Events;
use Civi\Api4\Event\SchemaMapBuildEvent;
use Civi\Api4\Service\Schema\Joinable\CustomGroupJoinable;
use Civi\Api4\Service\Schema\Joinable\Joinable;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Civi\Api4\Service\Schema\Joinable\OptionValueJoinable;
use CRM_Core_DAO_AllCoreTables as TableHelper;
use CRM_Core_BAO_CustomField as CustomFieldBAO;
use CRM_Utils_Array as ArrayHelper;

class SchemaMapBuilder
{
  /**
   * @var EventDispatcherInterface
   */
    protected $dispatcher;

  /**
   * @param EventDispatcherInterface $dispatcher
   */
    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

  /**
   * @return SchemaMap
   */
    public function build()
    {
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
    private function loadTables(SchemaMap $map)
    {
      /** @var \CRM_Core_DAO $daoName */
        foreach (TableHelper::get() as $daoName => $data) {
            $table = new Table($data['table']);
            foreach ($daoName::fields() as $field => $fieldData) {
                $this->addJoins($table, $field, $fieldData);
            }
            $map->addTable($table);
            $this->addCustomFields($map, $table, $data['name']);
        }

        $this->addBackReferences($map);
    }

  /**
   * @param Table $table
   * @param string $field
   * @param array $data
   */
    private function addJoins(Table $table, $field, array $data)
    {
        $fkClass = ArrayHelper::value('FKClassName', $data);

      // can there be multiple methods e.g. pseudoconstant and fkclass
        if ($fkClass) {
            $tableName = TableHelper::getTableForClass($fkClass);
            $fkKey = ArrayHelper::value('FKKeyColumn', $data, 'id');
            $joinable = new Joinable($tableName, $fkKey);
            $joinable->setJoinType($joinable::JOIN_TYPE_MANY_TO_ONE);
            $table->addTableLink($field, $joinable);
        } elseif (ArrayHelper::value('pseudoconstant', $data)) {
            $this->addPseudoConstantJoin($table, $field, $data);
        }
    }

  /**
   * @param Table $table
   * @param string $field
   * @param array $data
   */
    private function addPseudoConstantJoin(Table $table, $field, array $data)
    {
        $pseudoConstant = ArrayHelper::value('pseudoconstant', $data);
        $tableName = ArrayHelper::value('table', $pseudoConstant);
        $optionGroupName = ArrayHelper::value('optionGroupName', $pseudoConstant);
        $keyColumn = ArrayHelper::value('keyColumn', $pseudoConstant, 'id');

        if ($tableName) {
            $alias = str_replace('civicrm_', '', $tableName);
            $joinable = new Joinable($tableName, $keyColumn, $alias);
            $condition = ArrayHelper::value('condition', $pseudoConstant);
            if ($condition) {
                $joinable->addCondition($condition);
            }
            $table->addTableLink($field, $joinable);
        } elseif ($optionGroupName) {
            $keyColumn = ArrayHelper::value('keyColumn', $pseudoConstant, 'value');
            $joinable = new OptionValueJoinable($optionGroupName, null, $keyColumn);

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
    private function addBackReferences(SchemaMap $map)
    {
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
    private function getPlural($singular)
    {
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
     * @param SchemaMap                           $map
     * @param Table                               $baseTable
     * @param                                     $entityName
     */
    private function addCustomFields(SchemaMap $map, Table $baseTable, $entityName)
    {

        $parentTypes = ['Contact', 'Individual', 'Organization', 'Household'];
        if (in_array($entityName, $parentTypes)) {
            $entityName = $parentTypes;
        }

        $customFields = CustomFieldBAO::getFields($entityName, true);

        foreach ($customFields as $fieldData) {
            $tableName = ArrayHelper::value('table_name', $fieldData);

            $customTable = $map->getTableByName($tableName);
            if (!$customTable) {
                $customTable = new Table($tableName);
            }

            $group = ArrayHelper::value('option_group_id', $fieldData);
            if ($group) {
                $label = ArrayHelper::value('label', $fieldData);
                $columnName = ArrayHelper::value('column_name', $fieldData);
                $optionValueJoinable = new OptionValueJoinable($group, $label);
                $customTable->addTableLink($columnName, $optionValueJoinable);
            }

            $map->addTable($customTable);
            $alias = ArrayHelper::value('groupTitle', $fieldData);
            $isMultiple = ArrayHelper::value('is_multiple', $fieldData);
            $joinable = new CustomGroupJoinable($tableName, $alias, $isMultiple);
            $baseTable->addTableLink('id', $joinable);
        }
    }
}
