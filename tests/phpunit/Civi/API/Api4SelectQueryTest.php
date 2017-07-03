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
    $this->loadDataSet('DefaultDataSet');

    return parent::setUpHeadless();
  }

  public function testBasicSelect() {
    $query = new Api4SelectQuery('Contact', FALSE);
    $results = $query->run();

    $this->assertCount(2, $results);
    $this->assertEquals('Test Contact', array_shift($results)['display_name']);
  }

  public function testWithSingleWhereJoin() {
    $phoneNum = $this->getReference('test_phone_1')['phone'];

    $query = new Api4SelectQuery('Contact', FALSE);
    $query->where[] = array('phones.phone', '=', $phoneNum);
    $results = $query->run();

    $this->assertCount(1, $results);
  }

  public function testWithSelectAndWhereJoin() {
    $phoneNum = $this->getReference('test_phone_1')['phone'];

    $query = new Api4SelectQuery('Contact', FALSE);
    $query->select[] = 'id';
    $query->select[] = 'display_name';
    $query->select[] = 'phones.phone';
    $query->where[] = array('phones.phone', '=', $phoneNum);
    $results = $query->run();

    $this->assertCount(1, $results);
    $firstResult = array_shift($results);
    $this->assertArrayHasKey('phones', $firstResult);
    $firstPhone = array_shift($firstResult['phones']);
    $this->assertEquals($phoneNum, $firstPhone['phone']);
  }
}
