<?php

namespace Civi\Api4\Action\CustomField;

/**
 * Create a new field in a given CustomGroup.
 *
 * Pass an array of option_values to also create an option list for this field.
 */
class Create extends \Civi\Api4\Generic\DAOCreateAction {

  protected function formatCustomParams(&$params, $id) {
    if (!empty($params['option_values'])) {
      $weight = 0;
      foreach ($params['option_values'] as $key => $value) {
        // Translate simple key/value pairs into full-blown option values
        if (!is_array($value)) {
          $value = [
            'label' => $value,
            'value' => $key,
            'is_active' => 1,
            'weight' => $weight,
          ];
          $key = $weight++;
        }
        $params['option_label'][$key] = $value['label'];
        $params['option_value'][$key] = $value['value'];
        $params['option_status'][$key] = $value['is_active'];
        $params['option_weight'][$key] = $value['weight'];
      }
    }
    $params['option_type'] = !empty($params['option_values']);
  }

}
