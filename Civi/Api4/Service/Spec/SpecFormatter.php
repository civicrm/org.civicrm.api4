<?php

namespace Civi\Api4\Service\Spec;

use CRM_Utils_Array as ArrayHelper;

class SpecFormatter {
  /**
   * @param RequestSpec $spec
   *
   * @return array
   */
  public static function specToArray(RequestSpec $spec) {
    $specArray = array();
    $specArray['entity'] = $spec->getEntity();
    $specArray['action'] = $spec->getAction();
    $specArray['fields'] = array();

    foreach ($spec->getFields() as $field) {
      $specArray['fields'][$field->getName()] = array(
        'name' => $field->getName(),
        'title' => $field->getTitle(),
        'data_type' => $field->getDataType(),
        'default_value' => $field->getDefaultValue(),
        'description' => $field->getDescription(),
        'options' => $field->getOptions()
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
    $dataTypeName = self::getDataType($data);

    if (isset($data['custom_group_id'])) {
      $field = new CustomFieldSpec($name, $dataTypeName);
      $field->setCustomFieldId(ArrayHelper::value('id', $data));
      $field->setCustomGroupId($data['custom_group_id']);
    } else {
      $field = new FieldSpec($name, $dataTypeName);
    }

    $field->setDescription(ArrayHelper::value('description', $data));
    $field->setTitle(ArrayHelper::value('title', $data));
    $field->setRequired((bool) ArrayHelper::value('required', $data, FALSE));

    return $field;
  }

  /**
   * Get the data type from an array. Defaults to 'data_type' with fallback to
   * mapping for the integer value 'type'
   *
   * @param array $data
   *
   * @return string
   */
  private static function getDataType(array $data) {
    if (isset($data['data_type'])) {
      return $data['data_type'];
    }

    $dataTypeInt = ArrayHelper::value('type', $data);
    $dataTypeName = \CRM_Utils_Type::typeToString($dataTypeInt);

    return $dataTypeName;
  }
}
