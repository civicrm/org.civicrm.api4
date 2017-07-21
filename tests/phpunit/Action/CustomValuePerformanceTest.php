<?php

namespace Civi\Test\Api4\Action;

use Civi\Api4\Api\CustomFieldApi;
use Civi\Api4\Api\CustomGroupApi;
use Civi\Test\Api4\Traits\QueryCounterTrait;

/**
 * @group headless
 */
class CustomValuePerformanceTest extends BaseCustomValueTest {

  use QueryCounterTrait;

  public function testQueryCount() {

    $customGroup = CustomGroupApi::create()
      ->setCheckPermissions(FALSE)
      ->setValue('name', 'MyContactFields')
      ->setValue('title', 'MyContactFields')
      ->setValue('extends', 'Contact')
      ->execute();

    $customGroupId = $customGroup->getArrayCopy()['id'];

    CustomFieldApi::create()
      ->setCheckPermissions(FALSE)
      ->setValue('label', 'FavColor')
      ->setValue('custom_group_id', $customGroupId)
      ->setValue('options', ['r' => 'Red', 'g' => 'Green', 'b' => 'Blue'])
      ->setValue('html_type', 'Select')
      ->setValue('data_type', 'String')
      ->execute();

    CustomFieldApi::create()
      ->setCheckPermissions(FALSE)
      ->setValue('label', 'FavAnimal')
      ->setValue('custom_group_id', $customGroupId)
      ->setValue('html_type', 'Text')
      ->setValue('data_type', 'String')
      ->execute();

    CustomFieldApi::create()
      ->setCheckPermissions(FALSE)
      ->setValue('label', 'FavLetter')
      ->setValue('custom_group_id', $customGroupId)
      ->setValue('html_type', 'Text')
      ->setValue('data_type', 'String')
      ->execute();

    CustomFieldApi::create()
      ->setCheckPermissions(FALSE)
      ->setValue('label', 'FavFood')
      ->setValue('custom_group_id', $customGroupId)
      ->setValue('html_type', 'Text')
      ->setValue('data_type', 'String')
      ->execute();

    $this->beginQueryCount();

    Contact::create()
      ->setCheckPermissions(FALSE)
      ->setValue('first_name', 'Red')
      ->setValue('last_name', 'Tester')
      ->setValue('contact_type', 'Individual')
      ->setValue('MyContactFields.FavColor', 'r')
      ->setValue('MyContactFields.FavAnimal', 'Sheep')
      ->setValue('MyContactFields.FavLetter', 'z')
      ->setValue('MyContactFields.FavFood', 'Coconuts')
      ->execute();

    Contact::get()
      ->setCheckPermissions(FALSE)
      ->addSelect('display_name')
      ->addSelect('MyContactFields.FavColor.label')
      ->addSelect('MyContactFields.FavColor.weight')
      ->addSelect('MyContactFields.FavColor.is_default')
      ->addSelect('MyContactFields.FavAnimal')
      ->addSelect('MyContactFields.FavLetter')
      ->addWhere('MyContactFields.FavColor', '=', 'r')
      ->addWhere('MyContactFields.FavFood', '=', 'Coconuts')
      ->addWhere('MyContactFields.FavAnimal', '=', 'Sheep')
      ->addWhere('MyContactFields.FavLetter', '=', 'z')
      ->execute()
      ->first();

    // this is intentionally high since, but performance should be addressed
    $this->assertLessThan(400, $this->getQueryCount());
  }
}
