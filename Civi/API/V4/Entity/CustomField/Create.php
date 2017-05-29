<?php

namespace Civi\API\V4\Entity\CustomField;

use Civi\API\Result;
use Civi\API\V4\Action;

class Create extends Action\Create {

  const OPTION_TYPE_NEW = 1;
  const OPTION_STATUS_ACTIVE = 1;

  /**
   * @param Result $result
   */
  public function _run(Result $result) {

    $this->formatOptionParams();

    $optionType = $this->getValue('option_type');
    if (!$optionType) { // default to NULL
      $this->setValue('option_type', NULL);
    }

    parent::_run($result);
  }

  /**
   * Sets defaults required for option group and value creation
   * @see CRM_Core_BAO_CustomField::create()
   */
  protected function formatOptionParams() {
    $options = $this->getValue('options');

    if (!is_array($options)) {
      return;
    }

    $dataTypeKey = 'data_type';
    $optionLabelKey = 'option_label';
    $optionWeightKey = 'option_weight';
    $optionStatusKey = 'option_status';
    $optionValueKey = 'option_value';
    $optionTypeKey = 'option_type';

    $dataType = $this->getValue($dataTypeKey);
    $optionLabel = $this->getValue($optionLabelKey);
    $optionWeight = $this->getValue($optionWeightKey);
    $optionStatus = $this->getValue($optionStatusKey);
    $optionValue = $this->getValue($optionValueKey);
    $optionType = $this->getValue($optionTypeKey);

    if (!$optionType) {
      $this->setValue($optionTypeKey, self::OPTION_TYPE_NEW);
    }

    if (!$dataType) {
      $this->setValue($dataTypeKey, 'String');
    }

    if (!$optionLabel) {
      $this->setValue($optionLabelKey, array_values($options));
    }

    if (!$optionValue) {
      $this->setValue($optionValueKey, array_keys($options));
    }

    if (!$optionStatus) {
      $statuses = array_fill(0, count($options), self::OPTION_STATUS_ACTIVE);
      $this->setValue($optionStatusKey, $statuses);
    }

    if (!$optionWeight) {
      $this->setValue($optionWeightKey, range(1, count($options)));
    }
  }
}
