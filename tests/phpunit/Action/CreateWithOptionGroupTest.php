<?php

namespace Civi\Test\Api4\Action;

use Civi\Api4\GetParameterBag;

/**
 * @group headless
 */
class CreateWithOptionGroupTest extends BaseCustomValueTest {

  /**
   * Remove the custom tables
   */
  public function setUp() {
    $this->dropByPrefix('civicrm_value_financial');
    $this->dropByPrefix('civicrm_value_favorite');
    parent::setUp();
  }

  public function testGetWithCustomData() {
    $customGroupApi = \Civi::container()->get('custom_group.api');
    $customFieldApi = \Civi::container()->get('custom_field.api');
    $contactApi = \Civi::container()->get('contact.api');

    $customGroup = $customGroupApi->request('create', array(
      'name' => 'FavoriteThings',
      'extends' => 'Contact'
    ));

    $customGroupId = $customGroup->getArrayCopy()['id'];

    $customFieldApi->request('create', array(
      'label' => 'FavColor',
      'options' => array('r' => 'Red', 'g' => 'Green', 'b' => 'Blue'),
      'custom_group_id' => $customGroupId,
      'html_type' => 'Select',
      'data_type' => 'String'
    ));

    $customFieldApi->request('create', array(
      'label' => 'FavFood',
      'options' => array('1' => 'Corn', '2' => 'Potatoes', '3' => 'Cheese'),
      'custom_group_id' => $customGroupId,
      'html_type' => 'Select',
      'data_type' => 'String'
    ));

    $customGroup = $customGroupApi->request('create', array(
      'name' => 'FinancialStuff',
      'extends' => 'Contact'
    ));

    $customGroupId = $customGroup->getArrayCopy()['id'];


    $customFieldApi->request('create', array(
      'label' => 'Salary',
      'custom_group_id' => $customGroupId,
      'html_type' => 'Number',
      'data_type' => 'Money'
    ));

    $contactApi->request('create', array(
      'first_name' => 'Jerome',
      'last_name' => 'Tester',
      'contact_type' => 'Individual',
      'FavoriteThings.FavColor' => 'r',
      'FavoriteThings.FavFood' => '1',
      'FinancialStuff.Salary' => 50000
    ));

    $params = new GetParameterBag();
    $params->addSelect('first_name');
    $params->addSelect('FavoriteThings.FavColor.label');
    $params->addSelect('FavoriteThings.FavFood.label');
    $params->addSelect('FinancialStuff.Salary');
    $params->addWhere('FavoriteThings.FavFood.label', 'IN', ['Corn', 'Potatoes']);
    $params->addWhere('FinancialStuff.Salary', '>', '10000');

    $result = $contactApi->request('get', $params)->first();

    $this->assertArrayHasKey('FavoriteThings', $result);
    $favoriteThings = $result['FavoriteThings'];
    $favoriteFood = $favoriteThings['FavFood'];
    $favoriteColor = $favoriteThings['FavColor'];
    $financialStuff = $result['FinancialStuff'];
    $this->assertEquals('Red', $favoriteColor['label']);
    $this->assertEquals('Corn', $favoriteFood['label']);
    $this->assertEquals(50000, $financialStuff['Salary']);
  }

  public function testWithCustomDataForMultipleContacts() {
    $customGroupApi = \Civi::container()->get('custom_group.api');
    $customFieldApi = \Civi::container()->get('custom_field.api');
    $contactApi = \Civi::container()->get('contact.api');

    $customGroup = $customGroupApi->request('create', array(
      'name' => 'FavoriteThings',
      'extends' => 'Contact',
    ));

    $customGroupId = $customGroup->getArrayCopy()['id'];

    $customFieldApi->request('create', array(
      'label' => 'FavColor',
      'options' => ['r' => 'Red', 'g' => 'Green', 'b' => 'Blue'],
      'custom_group_id' => $customGroupId,
      'html_type' => 'Select',
      'data_type' => 'String',
    ));

    $customFieldApi->request('create', array(
      'label' => 'FavFood',
      'options' => ['1' => 'Corn', '2' => 'Potatoes', '3' => 'Cheese'],
      'custom_group_id' => $customGroupId,
      'html_type' => 'Select',
      'data_type' => 'String',
    ));

    $customGroup = $customGroupApi->request('create', array(
      'name' => 'FinancialStuff',
      'extends' => 'Contact',
    ));

    $customGroupId = $customGroup->getArrayCopy()['id'];

    $customFieldApi->request('create', array(
      'label' => 'Salary',
      'custom_group_id' => $customGroupId,
      'html_type' => 'Number',
      'data_type' => 'Money',
    ));

    $contactApi->request('create', array(
      'first_name' => 'Red',
      'last_name' => 'Corn',
      'contact_type' => 'Individual',
      'FavoriteThings.FavColor' => 'r',
      'FavoriteThings.FavFood' => '1',
      'FinancialStuff.Salary' => 10000
    ));

    $contactApi->request('create', array(
      'first_name' => 'Blue',
      'last_name' => 'Cheese',
      'contact_type' => 'Individual',
      'FavoriteThings.FavColor' => 'b',
      'FavoriteThings.FavFood' => '3',
      'FinancialStuff.Salary' => 500000
    ));

    $params = new GetParameterBag();
    $params->addSelect('first_name');
    $params->addSelect('last_name');
    $params->addSelect('FavoriteThings.FavColor.label');
    $params->addSelect('FavoriteThings.FavFood.label');
    $params->addSelect('FinancialStuff.Salary');
    $params->addWhere('FavoriteThings.FavFood.label', 'IN', ['Corn', 'Cheese']);
    $result = $contactApi->request('get', $params);

    $blueCheese = null;
    foreach ($result as $contact) {
      if ($contact['first_name'] === 'Blue') {
        $blueCheese = $contact;
      }
    }

    $this->assertEquals('Blue', $blueCheese['FavoriteThings']['FavColor']['label']);
    $this->assertEquals('Cheese', $blueCheese['FavoriteThings']['FavFood']['label']);
    $this->assertEquals(500000, $blueCheese['FinancialStuff']['Salary']);
  }
}
