<?php

namespace Civi\Api4\Utils;

class BAOFinder {
  /**
   * @param $entity
   *
   * @return \CRM_Core_DAO
   */
  public static function getBAOForEntity($entity) {
    $className = DAOFinder::getDaoNameForEntity($entity);
    $className = str_replace('DAO', 'BAO', $className);

    return new $className;
  }
}
