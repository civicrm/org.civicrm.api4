<?php

namespace Civi\Api4\Utils;

require_once 'api/v3/utils.php';

class CoreUtil {

  /**
   * todo this class should not rely on api3 code
   *
   * @param $entityName
   *
   * @return \CRM_Core_DAO|string
   *   The DAO name for use in static calls. Return doc block is hacked to allow
   *   auto-completion of static methods
   */
  public static function getDAOFromApiName($entityName) {
    if ($entityName === 'CustomValue' || strpos($entityName, 'Custom_') === 0) {
      return 'CRM_Contact_BAO_Contact';
    }
    return \_civicrm_api3_get_DAO($entityName);
  }

}
