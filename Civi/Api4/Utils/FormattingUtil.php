<?php

namespace Civi\Api4\Utils;

use CRM_Utils_Array as UtilsArray;

require_once 'api/v3/utils.php';

class FormattingUtil {

  /**
   * Transform raw api input to appropriate format for use in a SQL query.
   *
   * This is where the magic happens.
   *
   * @param $value
   * @param $fieldSpec
   * @throws \API_Exception
   */
  public static function formatValue(&$value, $fieldSpec, $entity) {
    if (is_array($value)) {
      foreach ($value as &$val) {
        self::formatValue($val, $fieldSpec, $entity);
      }
      return;
    }
    $fk = UtilsArray::value('fk_entity', $fieldSpec);
    if ($fieldSpec['name'] == 'id') {
      $fk = $entity;
    }
    $dataType = UtilsArray::value('data_type', $fieldSpec);

    if ($fk === 'Domain' && $value === 'current_domain') {
      $value = \CRM_Core_Config::domainID();
    }

    if ($fk === 'Contact' && !is_numeric($value)) {
      $value = \_civicrm_api3_resolve_contactID($value);
      if ('unknown-user' === $value) {
        throw new \API_Exception("\"{$fieldSpec['name']}\" \"{$value}\" cannot be resolved to a contact ID", 2002, array('error_field' => $fieldSpec['name'], "type" => "integer"));
      }
    }

    switch ($dataType) {
      case 'Timestamp':
        $value = date('Y-m-d H:i:s', strtotime($value));
    }
  }

}
