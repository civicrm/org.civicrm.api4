<?php

namespace Civi\Test\Api4\Action;

use Civi\Api4\GetParameterBag;

/**
 * @group headless
 */
class ExtendFromIndividualTest extends BaseCustomValueTest {

  public function testGetWithNonStandardExtends() {

    $customGroupApi = \Civi::container()->get('custom_group.api');
    $customFieldApi = \Civi::container()->get('custom_field.api');
    $contactApi = \Civi::container()->get('contact.api');

    $customGroup = $customGroupApi->request('create', array(
      'name' => 'MyContactFields',
      'extends' => 'Individual', // not Contact
    ), FALSE);

    $customFieldApi->request('create', array(
      'label' => 'FavColor',
      'custom_group_id' => $customGroup['id'],
      'html_type' => 'Text',
      'data_type' => 'String'
    ), FALSE);

    $contactId = $contactApi->request('create', array(
      'first_name' => 'Johann',
      'last_name' => 'Tester',
      'contact_type' => 'Individual',
      'MyContactFields.FavColor' => 'Red',
    ), FALSE)['id'];

    $params = new GetParameterBag();
    $params->addSelect('display_name');
    $params->addSelect('MyContactFields.FavColor');
    $params->addWhere('id', '=', $contactId);
    $contact = $contactApi->request('get', $params, FALSE)->first();

    $this->assertArrayHasKey('MyContactFields', $contact);
    $contactFields = $contact['MyContactFields'];
    $favColor = $contactFields['FavColor'];
    $this->assertEquals('Red', $favColor);
  }

}
