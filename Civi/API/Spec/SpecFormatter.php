<?php

namespace Civi\API\Spec;

class SpecFormatter {
  /**
   * @param RequestSpec $spec
   *
   * @return array
   */
  public static function toArray(RequestSpec $spec) {
    $specArray = array();

    foreach ($spec->getFields() as $field) {
      $specArray[$field->getName()] = array(
        'name' => $field->getName(),
        'title' => $field->getTitle(),
        'data_type' => $field->getDataType(),
        'default_value' => $field->getDefaultValue(),
        'description' => $field->getDescription()
      );
    }

    return $specArray;
  }
}
