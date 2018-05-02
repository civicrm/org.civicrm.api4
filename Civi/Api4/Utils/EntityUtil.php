<?php

namespace Civi\Api4\Utils;

use CRM_Core_DAO_AllCoreTables as AllTables;

/**
 * Class EntityUtil.
 *
 * @package Civi\Api4\Utils
 */
class EntityUtil {

  /**
   * @param string $table_name
   *
   * @return null|string
   */
  public static function getClassFromTable($table_name) {

    $className = AllTables::getClassForTable($table_name);

    return AllTables::getBriefName($className);
  }

}
