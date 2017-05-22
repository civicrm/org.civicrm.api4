<?php
namespace Civi\API\V4;
use Civi\Api4\CustomGroup;
use Civi\Api4\CustomField;
use Civi\Api4\Contact;

// fixme - what am I doing wrong to need this line?
require_once 'UnitTestCase.php';

/**
 * @group headless
 */
class EntityTest extends UnitTestCase {

  public function testGetWithCustomData() {
    $customGroup = CustomGroup::create()
      ->setCheckPermissions(FALSE)
      ->setValue('name', 'CustomContactFields')
      ->setValue('title', 'CustomContactFields')
      ->setValue('extends', 'Contact')
      ->execute();
    $customField = CustomField::create()
      ->setCheckPermissions(FALSE)
      ->setValue('name', 'Color')
      ->setValue('title', 'Color')
      ->setValue('options', ['r' => 'Red', 'g' => 'Green', 'b' => 'Blue'])
      ->setValue('custom_group_id', $customGroup->id)
      ->setValue('html_type', 'Select')
      ->execute();
    $contact = Contact::create()
      ->setCheckPermissions(FALSE)
      ->setValue('first_name', 'Red')
      ->setValue('last_name', 'Tester')
      ->setValue('CustomContactFields.Color', 'r')
      ->execute();
    $result = Contact::get()
      ->setCheckPermissions(FALSE)
      ->addWhere('CustomContactFields.Color.label', '=', 'Red')
      ->addSelect('CustomContactFields.Color')
      ->addSelect('CustomContactFields.Color.label')
      ->execute();
    $this->assertEquals('r', $result[$contact->id]['CustomContactFields.Color']);
    $this->assertEquals('Red', $result[$contact->id]['CustomContactFields.Color.label']);
  }

}
