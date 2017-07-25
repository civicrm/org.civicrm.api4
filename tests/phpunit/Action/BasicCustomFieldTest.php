<?php

namespace Civi\Test\Api4\Action;

use Civi\Api4\GetParameterBag;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use CRM_Utils_Array as ArrayHelper;

/**
 * @group headless
 */
class BasicCustomFieldTest extends BaseCustomValueTest {

  public function setUp() {
    $this->dropTables(array(
      'civicrm_custom_group',
      'civicrm_custom_field'
    ));
    $this->dropByPrefix('civicrm_value_mycontact');
    $this->loadDataSet('SingleCustomField');
  }

  public function tearDown() {
    $this->dropByPrefix('civicrm_value_mycontact');
  }

  public function testWithSingleField() {

    $contactApi = \Civi::container()->get('contact.api');

    $params = new ParameterBag();
    $params->set('first_name', 'Johann');
    $params->set('last_name', 'Tester');
    $params->set('contact_type', 'Individual');
    $params->set('MyContactFields.FavColor', 'Red');
    $contactId = $contactApi->request('create', $params, FALSE)['id'];

    $params = new GetParameterBag();
    $params->addSelect('first_name');
    $params->addSelect('MyContactFields.FavColor');
    $params->addWhere('id', '=', $contactId);
    $params->addWhere('MyContactFields.FavColor', '=', 'Red');
    $result = $contactApi->request('get', $params, FALSE);

    $this->assertCount(1, $result);
    $contact = $result->first();
    $this->assertArrayHasKey('MyContactFields', $contact);
    $contactFields = $contact['MyContactFields'];
    $this->assertArrayHasKey('FavColor', $contactFields);
    $this->assertEquals('Red', $contactFields['FavColor']);
  }

  public function testResettingToNull() {
    $contactApi = \Civi::container()->get('contact.api');

    $params = new ParameterBag();
    $params->set('first_name', 'Johann');
    $params->set('last_name', 'Tester');
    $params->set('contact_type', 'Individual');
    $params->set('MyContactFields.FavColor', 'Red');
    $contactId = $contactApi->request('create', $params, FALSE)['id'];

    $getParams = new GetParameterBag();
    $getParams->addSelect('first_name');
    $getParams->addSelect('MyContactFields.FavColor');
    $getParams->addWhere('id', '=', $contactId);

    $result = $contactApi->request('get', $getParams, FALSE)->first();

    $contactFields = ArrayHelper::value('MyContactFields', $result, array());
    $favColor = ArrayHelper::value('FavColor', $contactFields);
    if ($favColor !== 'Red') {
      $this->markTestIncomplete('Custom value was not set');
    }

    $params = new ParameterBag();
    $params->set('id', $contactId);
    $params->set('MyContactFields.FavColor', NULL);
    $contactApi->request('create', $params, FALSE);

    $newResult = $contactApi->request('get', $getParams, FALSE)->first();
    $contactFields = ArrayHelper::value('MyContactFields', $newResult, array());
    $favColor = ArrayHelper::value('FavColor', $contactFields);

    $this->assertEquals(NULL, $favColor);
  }

}
