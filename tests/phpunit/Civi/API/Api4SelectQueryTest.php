<?php

namespace phpunit\Civi\API;

use Civi\API\Api4SelectQuery;
use Civi\API\V4\UnitTestCase;
use Civi\TestDataLoaderTrait;

/**
 * @group headless
 */
class Api4SelectQueryTest extends UnitTestCase {

  use TestDataLoaderTrait;

  public function setUpHeadless() {
    $relatedTables = array(
      'civicrm_contact',
      'civicrm_contact',
      'civicrm_option_group',
      'civicrm_option_value',
      'civicrm_activity',
      'civicrm_activity_contact',
    );
    $this->cleanup(array('tablesToTruncate' => $relatedTables));
    $this->loadDataSet(__DIR__ . '/../API/V4/Action/DefaultDataSet.json');

    return parent::setUpHeadless();
  }

  public function testBasicSelect() {
    $query = new Api4SelectQuery('Contact', FALSE);
    $results = $query->run();

    $this->assertCount(2, $results);
    $this->assertEquals('Test Contact', array_shift($results)['display_name']);
  }

  public function testWithSingleWhereJoin() {
    $phoneNum = '+35355439483';

    $query = new Api4SelectQuery('Contact', FALSE);
    $query->where[] = array('phone.phone', '=', $phoneNum);
    $results = $query->run();

    $this->assertCount(1, $results);
  }

  public function testWithSelectAndWhereJoin() {
    $phoneNum = '+35355439483';

    $query = new Api4SelectQuery('Contact', FALSE);
    $query->select[] = 'phone.phone';
    $query->where[] = array('phone.phone', '=', $phoneNum);
    $results = $query->run();

    $this->assertCount(1, $results);
    $firstResult = array_shift($results);
    $this->assertArrayHasKey('phone', $firstResult);
    $firstPhone = array_shift($firstResult['phones']);
    $this->assertEquals($phoneNum, $firstPhone['phone']);
  }
}
