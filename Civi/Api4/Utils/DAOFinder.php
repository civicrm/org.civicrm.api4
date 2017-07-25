<?php

namespace Civi\Api4\Utils;

use CRM_Core_DAO_AllCoreTables as TableHelper;

class DAOFinder {
  /**
   * @param $entity
   *
   * @return NULL|string|\CRM_Core_DAO
   */
  public static function getDaoNameForEntity($entity) {
    return TableHelper::getFullName($entity);
  }
}
