<?php

namespace phpunit\Civi\API\V4\Action;

use Civi\API\V4\UnitTestCase;
use Civi\Api4\Activity;
use Civi\Api4\Contact;
use Civi\TestDataLoaderTrait;

/**
 * @group headless
 */
class FkJoinTest extends UnitTestCase {

  use TestDataLoaderTrait;

  public function setUpHeadless() {
    $relatedTables = array(
      'civicrm_contact',
      'civicrm_option_group',
      'civicrm_option_value',
      'civicrm_activity',
      'civicrm_activity_contact',
    );
    $this->cleanup(array('tablesToTruncate' => $relatedTables));
    $this->loadDataSet(__DIR__ . '/DefaultDataSet.json');

    return parent::setUpHeadless();
  }

  /**
   * Fetch all activities for housing support cases. Expects a single activity
   * loaded from the data set.
   */
  public function testThreeLevelJoin() {
    $results = Activity::get()
      ->setCheckPermissions(FALSE)
      ->addSelect('activity_type.option_group.name')
      ->addSelect('activity_type.option_group.is_active')
      ->addWhere('activity_type.option_group.name', '=', 'activity_type')
      ->execute();

    $names = array_column($results->getArrayCopy(), 'activity_type.option_group.name');
    $names = array_unique($names);

    $this->assertCount(2, $results);
    $this->assertCount(1, $names);
    $this->assertEquals(array('activity_type'), $names);
    $this->assertEquals(1, $results->first()['activity_type.option_group.is_active']);
  }

  public function testActivityContactJoin() {
    $results = Activity::get()
      ->setCheckPermissions(FALSE)
      ->addSelect('assignees.id')
      ->addSelect('assignees.first_name')
      ->addSelect('assignees.display_name')
      ->addWhere('assignees.first_name', '=', 'Test')
      ->execute();

    $firstResult = $results->first();

    $this->assertCount(1, $results);
    $this->assertTrue(is_array($firstResult['assignees']));

    $firstAssignee = array_shift($firstResult['assignees']);
    $this->assertEquals($firstAssignee['first_name'], 'Test');
  }

  public function testContactPhonesJoin() {
    $testContact = $this->getReference('test_contact_1');
    $testPhone = $this->getReference('test_phone_1');

    $results = Contact::get()
      ->setCheckPermissions(FALSE)
      ->addSelect('phones.phone')
      ->addWhere('contact.id', '=', $testContact['id'])
      ->addWhere('phones.location.name', '=', 'Home')
      ->execute()
      ->first();

    $this->assertArrayHasKey('phones', $results);
    $firstPhone = array_shift($results['phones']);
    $this->assertEquals($testPhone['phone'], $firstPhone);
  }
}
