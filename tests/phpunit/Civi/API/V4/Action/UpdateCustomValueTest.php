<?php
namespace Civi\API\V4\Action;

use Civi\Api4\Contact;
use Civi\Api4\CustomField;
use Civi\Api4\CustomGroup;

/**
 * @group headless
 */
class UpdateCustomValueTest extends BaseCustomValueTest {

  public function testGetWithCustomData() {

    $customGroup = CustomGroup::create()
      ->setCheckPermissions(FALSE)
      ->setValue('name', 'MyContactFields')
      ->setValue('title', 'MyContactFields')
      ->setValue('extends', 'Contact')
      ->execute()
      ->getArrayCopy();

    $tableName = $customGroup['table_name'];

    CustomField::create()
      ->setCheckPermissions(FALSE)
      ->setValue('label', 'FavColor')
      ->setValue('custom_group_id', $customGroup['id'])
      ->setValue('html_type', 'Text')
      ->setValue('data_type', 'String')
      ->execute();

    $contactId = Contact::create()
      ->setCheckPermissions(FALSE)
      ->setValue('first_name', 'Red')
      ->setValue('last_name', 'Tester')
      ->setValue('contact_type', 'Individual')
      ->setValue('MyContactFields.FavColor', 'Red')
      ->execute()
      ->getArrayCopy()['id'];

    Contact::create()
      ->setCheckPermissions(FALSE)
      ->setValue('id', $contactId)
      ->setValue('first_name', 'Red')
      ->setValue('last_name', 'Tester')
      ->setValue('contact_type', 'Individual')
      ->setValue('MyContactFields.FavColor', 'Blue')
      ->execute();

    $selectQuery = sprintf('SELECT * FROM `%s`;', $tableName);
    $result = \CRM_Core_DAO::executeQuery($selectQuery)->fetchAll();

    $this->assertEquals(1, count($result));
    $this->assertContains('Blue', array_shift($result));
  }

}
