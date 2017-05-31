<?php
namespace Civi\API\V4;

use Civi\Api4\CustomField;
use Civi\Api4\CustomGroup;
use Civi\Api4\Contact;
use phpunit\Civi\TableDropperTrait;

/**
 * @group headless
 */
class CreateWithOptionGroupTest extends UnitTestCase {

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
      ->setValue('options', ['r' => 'Red', 'g' => 'Green', 'b' => 'Blue'])
      ->setValue('custom_group_id', $customGroup->getArrayCopy()['id'])
      ->setValue('html_type', 'Select')
      ->setValue('data_type', 'String')
      ->execute();

    Contact::create()
      ->setCheckPermissions(FALSE)
      ->setValue('first_name', 'Red')
      ->setValue('last_name', 'Tester')
      ->setValue('contact_type', 'Individual')
      ->setValue('MyContactFields.Color', 'r')
      ->execute();

    $result = Contact::get()
      ->setCheckPermissions(FALSE)
      ->addSelect('display_name')
      ->addSelect('MyContactFields.Color')
      ->addSelect('MyContactFields.Color.label') // OptionValue.label
      ->addWhere('MyContactFields.Color', '=', 'r')
      ->execute()
      ->first();

    $this->assertEquals('r', $result['MyContactFields.Color']);
    $this->assertEquals('Red', $result['MyContactFields.Color.label']);
  }

}
