<?php

namespace Civi\Test\Api4\Action;

use Civi\Api4\Api\CustomFieldApi;
use Civi\Api4\Api\CustomGroupApi;

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
    $customGroup = CustomGroupApi::create()
      ->setCheckPermissions(FALSE)
      ->setValue('name', 'FavoriteThings')
      ->setValue('extends', 'Contact')
      ->execute();

    $customGroupId = $customGroup->getArrayCopy()['id'];

    CustomFieldApi::create()
      ->setCheckPermissions(FALSE)
      ->setValue('label', 'FavColor')
      ->setValue('options', ['r' => 'Red', 'g' => 'Green', 'b' => 'Blue'])
      ->setValue('custom_group_id', $customGroupId)
      ->setValue('html_type', 'Select')
      ->setValue('data_type', 'String')
      ->execute();

    CustomFieldApi::create()
      ->setCheckPermissions(FALSE)
      ->setValue('label', 'FavFood')
      ->setValue('options', ['1' => 'Corn', '2' => 'Potatoes', '3' => 'Cheese'])
      ->setValue('custom_group_id', $customGroupId)
      ->setValue('html_type', 'Select')
      ->setValue('data_type', 'String')
      ->execute();

    $customGroup = CustomGroupApi::create()
      ->setCheckPermissions(FALSE)
      ->setValue('name', 'FinancialStuff')
      ->setValue('extends', 'Contact')
      ->execute();

    $customGroupId = $customGroup->getArrayCopy()['id'];

    CustomFieldApi::create()
      ->setCheckPermissions(FALSE)
      ->setValue('label', 'Salary')
      ->setValue('custom_group_id', $customGroupId)
      ->setValue('html_type', 'Number')
      ->setValue('data_type', 'Money')
      ->execute();

    Contact::create()
      ->setCheckPermissions(FALSE)
      ->setValue('first_name', 'Jerome')
      ->setValue('last_name', 'Tester')
      ->setValue('contact_type', 'Individual')
      ->setValue('FavoriteThings.FavColor', 'r')
      ->setValue('FavoriteThings.FavFood', '1')
      ->setValue('FinancialStuff.Salary', 50000)
      ->execute();

    $result = Contact::get()
      ->setCheckPermissions(FALSE)
      ->addSelect('first_name')
      ->addSelect('FavoriteThings.FavColor.label')
      ->addSelect('FavoriteThings.FavFood.label')
      ->addSelect('FinancialStuff.Salary')
      ->addWhere('FavoriteThings.FavFood.label', 'IN', ['Corn', 'Potatoes'])
      ->addWhere('FinancialStuff.Salary', '>', '10000')
      ->execute()
      ->first();

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
    $customGroup = CustomGroupApi::create()
      ->setCheckPermissions(FALSE)
      ->setValue('name', 'FavoriteThings')
      ->setValue('extends', 'Contact')
      ->execute();

    $customGroupId = $customGroup->getArrayCopy()['id'];

    CustomFieldApi::create()
      ->setCheckPermissions(FALSE)
      ->setValue('label', 'FavColor')
      ->setValue('options', ['r' => 'Red', 'g' => 'Green', 'b' => 'Blue'])
      ->setValue('custom_group_id', $customGroupId)
      ->setValue('html_type', 'Select')
      ->setValue('data_type', 'String')
      ->execute();

    CustomFieldApi::create()
      ->setCheckPermissions(FALSE)
      ->setValue('label', 'FavFood')
      ->setValue('options', ['1' => 'Corn', '2' => 'Potatoes', '3' => 'Cheese'])
      ->setValue('custom_group_id', $customGroupId)
      ->setValue('html_type', 'Select')
      ->setValue('data_type', 'String')
      ->execute();

    $customGroup = CustomGroupApi::create()
      ->setCheckPermissions(FALSE)
      ->setValue('name', 'FinancialStuff')
      ->setValue('extends', 'Contact')
      ->execute();

    $customGroupId = $customGroup->getArrayCopy()['id'];

    CustomFieldApi::create()
      ->setCheckPermissions(FALSE)
      ->setValue('label', 'Salary')
      ->setValue('custom_group_id', $customGroupId)
      ->setValue('html_type', 'Number')
      ->setValue('data_type', 'Money')
      ->execute();

    Contact::create()
      ->setCheckPermissions(FALSE)
      ->setValue('first_name', 'Red')
      ->setValue('last_name', 'Corn')
      ->setValue('contact_type', 'Individual')
      ->setValue('FavoriteThings.FavColor', 'r')
      ->setValue('FavoriteThings.FavFood', '1')
      ->setValue('FinancialStuff.Salary', 10000)
      ->execute();

    Contact::create()
      ->setCheckPermissions(FALSE)
      ->setValue('first_name', 'Blue')
      ->setValue('last_name', 'Cheese')
      ->setValue('contact_type', 'Individual')
      ->setValue('FavoriteThings.FavColor', 'b')
      ->setValue('FavoriteThings.FavFood', '3')
      ->setValue('FinancialStuff.Salary', 500000)
      ->execute();

    $result = Contact::get()
      ->setCheckPermissions(FALSE)
      ->addSelect('first_name')
      ->addSelect('last_name')
      ->addSelect('FavoriteThings.FavColor.label')
      ->addSelect('FavoriteThings.FavFood.label')
      ->addSelect('FinancialStuff.Salary')
      ->addWhere('FavoriteThings.FavFood.label', 'IN', ['Corn', 'Cheese'])
      ->execute();

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
