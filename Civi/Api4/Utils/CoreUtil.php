<?php

namespace Civi\Api4\Utils;

use Civi\Api4\CustomGroup;
use CRM_Core_DAO_AllCoreTables as AllCoreTables;

require_once 'api/v3/utils.php';

class CoreUtil {

  /**
   * todo this class should not rely on api3 code
   *
   * @param $entityName
   *
   * @return \CRM_Core_DAO|string
   *   The BAO name for use in static calls. Return doc block is hacked to allow
   *   auto-completion of static methods
   */
  public static function getBAOFromApiName($entityName) {
    if ($entityName === 'CustomValue' || strpos($entityName, 'Custom_') === 0) {
      return 'CRM_Contact_BAO_Contact';
    }
    return \_civicrm_api3_get_BAO($entityName);
  }

  /**
   * Get table name of given Custom group
   *
   * @param string $customGroupName
   *
   * @return string
   */
  public static function getCustomTableByName($customGroupName) {
    return CustomGroup::get()
      ->addSelect('table_name')
      ->addWhere('name', '=', $customGroupName)
      ->execute()
      ->first()['table_name'];
  }

  /**
   * Given a sql table name, return the name of the api entity.
   *
   * @param $tableName
   * @return string
   */
  public static function getApiNameFromTableName($tableName) {
    return AllCoreTables::getBriefName(AllCoreTables::getClassForTable($tableName));
  }

  /**
   * Get custom table links for given entity
   *
   * @param string $entity
   *
   * @return array
   */
  public static function getCustomTableLinks($entity) {
    // Don't be silly
    if (!array_key_exists($entity, \CRM_Core_SelectValues::customGroupExtends())) {
      return [];
    }

    $queryEntity = (array) $entity;
    if ($entity == 'Contact') {
      $queryEntity = ['Contact', 'Individual', 'Organization', 'Household'];
    }

    $links = [];
    $fieldData = \CRM_Utils_SQL_Select::from('civicrm_custom_field f')
      ->join('custom_group', 'INNER JOIN civicrm_custom_group g ON g.id = f.custom_group_id')
      ->select(['g.name as custom_group_name', 'g.table_name', 'g.is_multiple', 'f.name', 'label', 'column_name', 'option_group_id'])
      ->where('g.extends IN (@entity)', ['@entity' => $queryEntity])
      ->where('g.is_active')
      ->where('f.is_active')
      ->execute();

    while ($fieldData->fetch()) {
      $alias = $fieldData->custom_group_name;
      $links[$alias]['tableName'] = $fieldData->table_name;
      $links[$alias]['label'] = $fieldData->label;
      $links[$alias]['option_group_id'] = $fieldData->option_group_id;
      $links[$alias]['isMultiple'] = !empty($fieldData->is_multiple);
      $links[$alias]['columns'][$fieldData->name] = $fieldData->column_name;
    }

    return $links;
  }

}
