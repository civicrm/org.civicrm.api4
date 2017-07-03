<?php

namespace phpunit\Civi\API;

use Civi\API\Api4SelectQuery;
use Civi\API\V4\UnitTestCase;
use Civi\TestDataLoaderTrait;

/**
 * @group headless
 */
class Api4SelectQueryComplexJoinTest extends UnitTestCase {

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
    $this->loadDataSet('SingleContactWithPhoneEmailAndActivities');
    return parent::setUpHeadless();
  }

  public function testWithComplexRelatedEntitySelect() {
    $query = new Api4SelectQuery('Contact', FALSE);
    $query->select[] = 'id';
    $query->select[] = 'display_name';
    $query->select[] = 'phone.phone';
    $query->select[] = 'email.email';
    $query->select[] = 'email.location_type.name';
    $query->select[] = 'created_activities.contact_id';
    $query->select[] = 'created_activities.activity.subject';
    $query->select[] = 'created_activities.activity.activity_type.name';
    $results = $query->run();

    $testActivities = [
      $this->getReference('test_activity_1'),
      $this->getReference('test_activity_2'),
    ];
    $activitySubjects = array_column($testActivities, 'subject');

    $this->assertCount(1, $results);
    $firstResult = array_shift($results);
    $this->assertArrayHasKey('created_activities', $firstResult);
    $firstCreatedActivity = array_shift($firstResult['created_activities']);
    $this->assertArrayHasKey('activity', $firstCreatedActivity);
    $firstActivity = array_shift($firstCreatedActivity['activity']);
    $this->assertContains($firstActivity['subject'], $activitySubjects);
    $this->assertArrayHasKey('activity_type', $firstActivity);
    $activityType = array_shift($firstActivity['activity_type']);
    $this->assertArrayHasKey('name', $activityType);
  }
}
