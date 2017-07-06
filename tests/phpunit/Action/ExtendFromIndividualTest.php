<?php

namespace Civi\Test\API\V4\Action;

use Civi\API\V4\Entity\Contact;
use Civi\API\V4\Entity\CustomField;
use Civi\API\V4\Entity\CustomGroup;

/**
 * @group headless
 */
class ExtendFromIndividualTest extends BaseCustomValueTest {

  public function testGetWithNonStandardExtends() {

    $customGroup = CustomGroup::create()
      ->setCheckPermissions(FALSE)
      ->setValue('name', 'MyContactFields')
      ->setValue('extends', 'Individual') // not Contact
      ->execute()
      ->getArrayCopy();

    CustomField::create()
      ->setCheckPermissions(FALSE)
      ->setValue('label', 'FavColor')
      ->setValue('custom_group_id', $customGroup['id'])
      ->setValue('html_type', 'Text')
      ->setValue('data_type', 'String')
      ->execute();

    $contactId = Contact::create()
      ->setCheckPermissions(FALSE)
      ->setValue('first_name', 'Johann')
      ->setValue('last_name', 'Tester')
      ->setValue('contact_type', 'Individual')
      ->setValue('MyContactFields.FavColor', 'Red')
      ->execute()
      ->getArrayCopy()['id'];

    $contact = Contact::get()
      ->setCheckPermissions(FALSE)
      ->addSelect('display_name')
      ->addSelect('MyContactFields.FavColor')
      ->addWhere('id', '=', $contactId)
      ->execute()
      ->first();

    $this->assertArrayHasKey('MyContactFields', $contact);
    $contactFields = $contact['MyContactFields'];
    $favColor = $contactFields['FavColor'];
    $this->assertEquals('Red', $favColor);
  }

}
