<?php

namespace Civi\Test\Api4\Action;

use Civi\Api4\GetParameterBag;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

/**
 * @group headless
 */
class BasicCustomFieldTest extends BaseCustomValueTest {

  public function testWithSingleField() {

    $container = \Civi::container();
    $customGroupApi = $container->get('custom_group.api');
    $customFieldApi = $container->get('custom_field.api');
    $contactApi = $container->get('contact.api');

    $params = new ParameterBag();
    $params->set('name', 'MyContactFields');
    $params->set('extends', 'Contact');
    $customGroup = $customGroupApi->request('create', $params);

    $params = new ParameterBag();
    $params->set('label', 'FavColor');
    $params->set('custom_group_id', $customGroup['id']);
    $params->set('html_type', 'Text');
    $params->set('data_type', 'String');
    $customFieldApi->request('create', $params);

    $params = new ParameterBag();
    $params->set('first_name', 'Johann');
    $params->set('last_name', 'Tester');
    $params->set('contact_type', 'Individual');
    $params->set('MyContactFields.FavColor', 'Red');
    $contactId = $contactApi->request('create', $params)['id'];

    $params = new GetParameterBag();
    $params->addSelect('first_name');
    $params->addSelect('MyContactFields.FavColor');
    $params->addWhere('id', '=', $contactId);
    $params->addWhere('MyContactFields.FavColor', '=', 'Red');
    $result = $contactApi->request('get', $params);

    $this->assertCount(1, $result);
    $contact = $result->first();
    $this->assertArrayHasKey('MyContactFields', $contact);
    $contactFields = $contact['MyContactFields'];
    $this->assertArrayHasKey('FavColor', $contactFields);
    $this->assertEquals('Red', $contactFields['FavColor']);
  }

}
