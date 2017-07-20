<?php

namespace Civi\Test\Api4\Action;

use Civi\Api4\GetParameterBag;
use Civi\Test\Api4\UnitTestCase;

/**
 * @group headless
 */
class FkJoinTest extends UnitTestCase {

  public function setUpHeadless() {
    $relatedTables = array(
      'civicrm_contact',
      'civicrm_option_group',
      'civicrm_option_value',
      'civicrm_activity',
      'civicrm_phone',
      'civicrm_activity_contact',
    );
    $this->cleanup(array('tablesToTruncate' => $relatedTables));
    $this->loadDataSet('DefaultDataSet');

    return parent::setUpHeadless();
  }

  /**
   * Fetch all activities for housing support cases. Expects a single activity
   * loaded from the data set.
   */
  public function testThreeLevelJoin() {
    $activityApi = \Civi::container()->get('activity.api');
    $params = new GetParameterBag();
    $params->addWhere('activity_type.name', '=', 'housing_support');
    $results = $activityApi->request('get', $params, FALSE);

    $this->assertCount(1, $results);
  }

  public function testActivityContactJoin() {
    $activityApi = \Civi::container()->get('activity.api');
    $params = new GetParameterBag();
    $params->addSelect('assignees.id');
    $params->addSelect('assignees.first_name');
    $params->addSelect('assignees.display_name');
    $params->addWhere('assignees.first_name', '=', 'Test');
    $results = $activityApi->request('get', $params, FALSE);

    $firstResult = $results->first();

    $this->assertCount(1, $results);
    $this->assertTrue(is_array($firstResult['assignees']));

    $firstAssignee = array_shift($firstResult['assignees']);
    $this->assertEquals($firstAssignee['first_name'], 'Test');
  }

  public function testContactPhonesJoin() {
    $contactApi = \Civi::container()->get('contact.api');

    $testContact = $this->getReference('test_contact_1');
    $testPhone = $this->getReference('test_phone_1');

    $params = new GetParameterBag();
    $params->addSelect('phones.phone');
    $params->addWhere('id', '=', $testContact['id']);
    $params->addWhere('phones.location_type.name', '=', 'Home');
    $results = $contactApi->request('get', $params, FALSE)->first();

    $this->assertArrayHasKey('phones', $results);
    $this->assertCount(2, $results['phones']);
    $firstPhone = array_shift($results['phones']);
    $this->assertEquals($testPhone['phone'], $firstPhone['phone']);
  }
}
