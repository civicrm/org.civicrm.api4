<?php

namespace Civi\Api4\Service\Spec\Provider;

use Civi\Api4\Service\Spec\FieldSpec;
use Civi\Api4\Service\Spec\RequestSpec;


class AddressCreationSpecProvider implements SpecProviderInterface {

  /**
   * @param RequestSpec $spec
   */
  public function modifySpec(RequestSpec $spec) {
    $action = $spec->getAction();
    $spec->getFieldByName('contact_id')->setRequired(TRUE);
    $spec->getFieldByName('location_type_id')->setRequired(TRUE);

    $extraFields = [
      'world_region' => [
        'type' => 'String',
        'title' => ts('World Region'),
      ],
    ];
    if ($action !== 'get') {
      $extraFields = array_merge($extraFields, [
        'street_parsing' => [
          'type' => 'Boolean',
          'title' => ts('Street Parsing'),
          'description' => ts('Optional param to indicate you want the street_address field parsed into individual params'),
        ],
        'skip_geocode' => [
          'type' => 'Boolean',
          'title' => ts('Skip geocode'),
          'description' => ts('Optional param to indicate you want to skip geocoding (useful when importing a lot of addresses at once, the job \'Geocode and Parse Addresses\' can execute this task after the import)'),
        ],
        'fix_address' => [
          'type' => 'Boolean',
          'title' => ts('Fix address'),
          'description' => ts('When true, apply various fixes to the address before insert. Default true.'),
          'default' => TRUE,
        ],
      ]);
    }
    foreach ($extraFields as $name => $field) {
      $fieldSpec = new FieldSpec($name, 'Address', $field['type']);
      $fieldSpec->setTitle($field['title']);
      if (!empty($field['description'])) {
        $fieldSpec->setDescription($field['description']);
      }
      if (!empty($field['default'])) {
        $fieldSpec->setDefaultValue($field['default']);
      }

      $spec->addFieldSpec($fieldSpec);
    }
  }

  /**
   * @param string $entity
   * @param string $action
   *
   * @return bool
   */
  public function applies($entity, $action) {
    return $entity === 'Address' && ($action === 'create' || $action === 'get');
  }

}
