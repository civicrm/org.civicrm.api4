<?php

namespace Civi\Test\Api4\Action;

use Civi\Api4\Entity\CustomField;
use Civi\Api4\Entity\CustomGroup;
use Civi\Api4\Entity\OptionGroup;
use Civi\Api4\Entity\OptionValue;

/**
 * @group headless
 */
class CreateCustomValueTest extends BaseCustomValueTest {

  public function testGetWithCustomData() {
    $optionValues = ['r' => 'Red', 'g' => 'Green', 'b' => 'Blue'];

    $customGroup = CustomGroup::create()
      ->setCheckPermissions(FALSE)
      ->setValue('name', 'MyContactFields')
      ->setValue('extends', 'Contact')
      ->execute();

    CustomField::create()
      ->setCheckPermissions(FALSE)
      ->setValue('label', 'Color')
      ->setValue('options', $optionValues)
      ->setValue('custom_group_id', $customGroup->getArrayCopy()['id'])
      ->setValue('html_type', 'Select')
      ->setValue('data_type', 'String')
      ->execute();

    $customField = CustomField::get()
      ->setCheckPermissions(FALSE)
      ->addWhere('label', '=', 'Color')
      ->execute()
      ->first();

    $this->assertNotNull($customField['option_group_id']);
    $optionGroupId = $customField['option_group_id'];

    $optionGroup = OptionGroup::get()
      ->setCheckPermissions(FALSE)
      ->addWhere('id', '=', $optionGroupId)
      ->execute()
      ->first();

    $this->assertEquals('Color', $optionGroup['title']);

    $createdOptionValues = OptionValue::get()
      ->setCheckPermissions(FALSE)
      ->addWhere('option_group_id', '=', $optionGroupId)
      ->execute()
      ->getArrayCopy();

    $values = array_column($createdOptionValues, 'value');
    $labels = array_column($createdOptionValues, 'label');
    $createdOptionValues = array_combine($values, $labels);

    $this->assertEquals($optionValues, $createdOptionValues);
  }

}
