<?php

namespace Civi\Api4\Event\Subscriber;

use Civi\Api4\Request;

class CustomFieldPreCreationSubscriber extends PreCreationSubscriber {

  const OPTION_TYPE_NEW = 1;
  const OPTION_STATUS_ACTIVE = 1;

  /**
   * @inheritdoc
   */
  public function modify(Request $request) {
    $this->formatOptionParams($request);
    $this->setDefaults($request);
  }

  /**
   * @inheritdoc
   */
  protected function applies(Request $request) {
    return $request->getEntity() === 'CustomField';
  }

  /**
   * Sets defaults required for option group and value creation
   * @see CRM_Core_BAO_CustomField::create()
   *
   * @param Request $request
   */
  protected function formatOptionParams(Request $request) {
    $options = $request->get('options');

    if (!is_array($options)) {
      return;
    }

    $dataTypeKey = 'data_type';
    $optionLabelKey = 'option_label';
    $optionWeightKey = 'option_weight';
    $optionStatusKey = 'option_status';
    $optionValueKey = 'option_value';
    $optionTypeKey = 'option_type';

    $dataType = $request->get($dataTypeKey);
    $optionLabel = $request->get($optionLabelKey);
    $optionWeight = $request->get($optionWeightKey);
    $optionStatus = $request->get($optionStatusKey);
    $optionValue = $request->get($optionValueKey);
    $optionType = $request->get($optionTypeKey);

    if (!$optionType) {
      $request->set($optionTypeKey, self::OPTION_TYPE_NEW);
    }

    if (!$dataType) {
      $request->set($dataTypeKey, 'String');
    }

    if (!$optionLabel) {
      $request->set($optionLabelKey, array_values($options));
    }

    if (!$optionValue) {
      $request->set($optionValueKey, array_keys($options));
    }

    if (!$optionStatus) {
      $statuses = array_fill(0, count($options), self::OPTION_STATUS_ACTIVE);
      $request->set($optionStatusKey, $statuses);
    }

    if (!$optionWeight) {
      $request->set($optionWeightKey, range(1, count($options)));
    }
  }

  /**
   * @param Request $request
   */
  private function setDefaults(Request $request) {
    if (!$request->get('option_type')) {
      $request->set('option_type', NULL);
    }
  }
}
