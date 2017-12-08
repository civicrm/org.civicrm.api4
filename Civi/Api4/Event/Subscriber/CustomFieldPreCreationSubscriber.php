<?php

namespace Civi\Api4\Event\Subscriber;

use Civi\Api4\Action\Create;

class CustomFieldPreCreationSubscriber extends PreCreationSubscriber {

  const OPTION_TYPE_NEW = 1;
  const OPTION_STATUS_ACTIVE = 1;

  /**
   * @param Create $request
   */
  public function modify(Create $request) {
    $this->formatOptionParams($request);
    $this->setDefaults($request);
  }

  /**
   * @param Create $request
   *
   * @return bool
   */
  protected function applies(Create $request) {
    return $request->getEntity() === 'CustomField';
  }

  /**
   * Sets defaults required for option group and value creation
   * @see CRM_Core_BAO_CustomField::create()
   *
   * @param Create $request
   */
  protected function formatOptionParams(Create $request) {
    $options = $request->getValue('options');

    if (!is_array($options)) {
      return;
    }

    $dataTypeKey = 'data_type';
    $optionLabelKey = 'option_label';
    $optionWeightKey = 'option_weight';
    $optionStatusKey = 'option_status';
    $optionValueKey = 'option_value';
    $optionTypeKey = 'option_type';

    $dataType = $request->getValue($dataTypeKey);
    $optionLabel = $request->getValue($optionLabelKey);
    $optionWeight = $request->getValue($optionWeightKey);
    $optionStatus = $request->getValue($optionStatusKey);
    $optionValue = $request->getValue($optionValueKey);
    $optionType = $request->getValue($optionTypeKey);

    if (!$optionType) {
      $request->setValue($optionTypeKey, self::OPTION_TYPE_NEW);
    }

    if (!$dataType) {
      $request->setValue($dataTypeKey, 'String');
    }

    if (!$optionLabel) {
      $request->setValue($optionLabelKey, array_values($options));
    }

    if (!$optionValue) {
      $request->setValue($optionValueKey, array_keys($options));
    }

    if (!$optionStatus) {
      $statuses = array_fill(0, count($options), self::OPTION_STATUS_ACTIVE);
      $request->setValue($optionStatusKey, $statuses);
    }

    if (!$optionWeight) {
      $request->setValue($optionWeightKey, range(1, count($options)));
    }
  }

  /**
   * @param Create $request
   */
  private function setDefaults(Create $request) {
    if (!$request->getValue('option_type')) {
      $request->setValue('option_type', NULL);
    }
  }

}
