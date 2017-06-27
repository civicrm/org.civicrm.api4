<?php

namespace Civi\API\Spec\Provider;

use Civi\API\Spec\RequestSpec;
use Civi\API\Spec\SpecFormatter;
use Civi\Api4\CustomField;
use Civi\Api4\CustomGroup;
use CRM_Utils_Array as ArrayHelper;

class CustomFieldSpecProvider implements SpecProviderInterface {
  /**
   * Adds custom fields to the specification with names in the form:
   * CustomGroupName.CustomFieldName
   *
   * @param RequestSpec $spec
   */
  public function modifySpec(RequestSpec $spec) {
    $customGroups = CustomGroup::get()
      ->setCheckPermissions(FALSE)
      ->addWhere('extends','=', $spec->getEntity())
      ->execute()
      ->getArrayCopy();

    if (empty($customGroups)) {
      return;
    }

    // index by ID so we can use this as a cache of names later in this method
    $customGroupNames = array_column($customGroups, 'name', 'id');
    $customGroupIds = array_keys($customGroupNames);

    $customFields = CustomField::get()
      ->setCheckPermissions(FALSE)
      ->addWhere('custom_group_id', 'IN', $customGroupIds)
      ->execute();

    foreach ($customFields as $customField) {
      $customGroupId = ArrayHelper::value('custom_group_id', $customField);
      $customGroupName = ArrayHelper::value($customGroupId, $customGroupNames);
      $customFieldName = ArrayHelper::value('name', $customField);

      $fieldSpec = SpecFormatter::arrayToField($customField);
      $fieldName = sprintf('%s.%s', $customGroupName, $customFieldName);
      $fieldSpec->setName($fieldName);

      $spec->addFieldSpec($fieldSpec);
    }
  }

  /**
   * We only want to apply this to entities that can have custom fields
   *
   * @param string $entity
   * @param string $action
   *
   * @return bool
   */
  public function applies($entity, $action) {
    return !in_array(
      $entity,
      array('CustomField', 'CustomGroup')
    );
  }
}
