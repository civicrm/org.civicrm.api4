<?php

namespace Civi\Test\Api4\Query;

use Civi\Api4\Query\Api4SelectQuery;
use Civi\Test\Api4\UnitTestCase;

/**
 * @group headless
 */
class Api4SelectQueryComplexJoinTest extends UnitTestCase {

  public function setUpHeadless() {
    $relatedTables = array(
      'civicrm_contact',
      'civicrm_option_group',
      'civicrm_option_value',
      'civicrm_email',
      'civicrm_phone',
      'civicrm_activity',
      'civicrm_activity_contact',
    );
    $this->cleanup(array('tablesToTruncate' => $relatedTables));
    $this->loadDataSet('SingleContact');
    return parent::setUpHeadless();
  }

  public function testWithComplexRelatedEntitySelect() {
    $query = new Api4SelectQuery('Contact', FALSE);
    $query->select[] = 'id';
    $query->select[] = 'display_name';
    $query->select[] = 'phones.phone';
    $query->select[] = 'emails.email';
    $query->select[] = 'emails.location_type.name';
    $query->select[] = 'source_activities.subject';
    $query->select[] = 'source_activities.activity_type.name';
    $query->where[] = array('first_name', '=', 'Single');
    $results = $query->run();

    $testActivities = [
      $this->getReference('test_activity_1'),
      $this->getReference('test_activity_2'),
    ];
    $activitySubjects = array_column($testActivities, 'subject');

    $this->assertCount(1, $results);
    $firstResult = array_shift($results);
    $this->assertArrayHasKey('source_activities', $firstResult);
    $sourceActivity = array_shift($firstResult['source_activities']);
    $this->assertContains($sourceActivity['subject'], $activitySubjects);
    $this->assertArrayHasKey('activity_type', $sourceActivity);
    $activityType = $sourceActivity['activity_type'];
    $this->assertArrayHasKey('name', $activityType);
  }

  public function testWithSelectOfOrphanDeepValues() {
    $query = new Api4SelectQuery('Contact', FALSE);
    $query->select[] = 'id';
    $query->select[] = 'first_name';
    $query->select[] = 'emails.location_type.name'; // emails not selected
    $results = $query->run();
    $firstResult = array_shift($results);

    $this->assertEmpty($firstResult['emails']);
  }

  public function testOrderDoesNotMatter() {
    $query = new Api4SelectQuery('Contact', FALSE);
    $query->select[] = 'id';
    $query->select[] = 'first_name';
    $query->select[] = 'emails.location_type.name'; // before emails selection
    $query->select[] = 'emails.email';
    $results = $query->run();
    $firstResult = array_shift($results);

    $this->assertNotEmpty($firstResult['emails'][0]['location_type']['name']);
  }
}
