<?php
namespace Civi\API\V4\Action;

use Civi\Api4\CustomField;
use Civi\Api4\CustomGroup;
use Civi\Api4\Contact;

/**
 * @group headless
 */
class CreateWithOptionGroupTest extends BaseCustomValueTest {

  public function testGetWithCustomData() {
    $customGroup = CustomGroup::create()
      ->setCheckPermissions(FALSE)
      ->setValue('name', 'MyContactFields')
      ->setValue('title', 'MyContactFields')
      ->setValue('extends', 'Contact')
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
