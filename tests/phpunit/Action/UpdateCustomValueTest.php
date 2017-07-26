<?php

namespace Civi\Test\Api4\Action;

use \CRM_Core_BAO_CustomValueTable as CustomValueTable;

/**
 * @group headless
 */
class UpdateCustomValueTest extends BaseCustomValueTest {

  public function testGetWithCustomData() {

    $customGroupApi = \Civi::container()->get('custom_group.api');
    $customFieldApi = \Civi::container()->get('custom_field.api');
    $contactApi = \Civi::container()->get('contact.api');

    $customGroup = $customGroupApi->request('create', array(
      'name' => 'MyContactFields',
      'extends' => 'Contact',
    ), FALSE);

    $customFieldApi->request('create', array(
      'label' => 'FavColor',
      'custom_group_id' => $customGroup['id'],
      'html_type' => 'Text',
      'data_type' => 'String',
    ), FALSE);

    $contactId = $contactApi->request('create', array(
      'first_name' => 'Red',
      'last_name' => 'Tester',
      'contact_type' => 'Individual',
      'MyContactFields.FavColor' => 'Red',
    ), FALSE)['id'];

    $contactApi->request('create', array(
      'id' => $contactId,
      'first_name' => 'Red',
      'last_name' => 'Tester',
      'contact_type' => 'Individual',
      'MyContactFields.FavColor' => 'Blue',
    ), FALSE);

    $result = CustomValueTable::getEntityValues($contactId, 'Contact');

    $this->assertEquals(1, count($result));
    $this->assertContains('Blue', $result);
  }

}
