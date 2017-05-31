<?php
namespace Civi\API\V4;

use Civi\Api\TableDropperTrait;
use Civi\Api4\CustomField;
use Civi\Api4\CustomGroup;
use Civi\Api4\OptionGroup;
use Civi\Api4\OptionValue;

/**
 * @group headless
 */
class CreateCustomValueTest extends UnitTestCase {

  use TableDropperTrait;

  /**
   * Set up baseline for testing
   */
  public function setUp() {
    $cleanup_params = array(
      'tablesToTruncate' => array(
        'civicrm_custom_group',
        'civicrm_custom_field',
        'civicrm_contact',
        'civicrm_option_group',
        'civicrm_option_value'
      ),
    );

    $this->dropByPrefix('civicrm_value_mycontact');
    $this->cleanup($cleanup_params);
  }

  public function testGetWithCustomData() {
    $optionValues = ['r' => 'Red', 'g' => 'Green', 'b' => 'Blue'];

    $customGroup = CustomGroup::create()
      ->setCheckPermissions(FALSE)
      ->setValue('name', 'MyContactFields')
      ->setValue('title', 'MyContactFields')
      ->setValue('extends', 'Contact')
      ->setValue('is_active', 1) // todo this should be default
      ->execute();

    CustomField::create()
      ->setCheckPermissions(FALSE)
      ->setValue('label', 'Color')
      ->setValue('title', 'Color')
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
