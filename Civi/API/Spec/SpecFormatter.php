<?php

namespace Civi\API\Spec;

use CRM_Utils_Array as ArrayHelper;

class SpecFormatter {
  /**
   * @param RequestSpec $spec
   *
   * @return array
   */
  public static function specToArray(RequestSpec $spec) {
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

  /**
   * @param array $data
   *
   * @return FieldSpec
   */
  public static function arrayToField(array $data) {
    $name = ArrayHelper::value('name', $data);
    $dataTypeInt = ArrayHelper::value('type', $data);
    $dataTypeName = \CRM_Utils_Type::typeToString($dataTypeInt);
    $field = new FieldSpec($name, $dataTypeName);

    $field->setDescription(ArrayHelper::value('description', $data));
    $field->setTitle(ArrayHelper::value('title', $data));
    $field->setRequired((bool) ArrayHelper::value('required', $data, FALSE));

    return$field;
  }
}
