<?php

namespace Civi\Test\Api4\Query;

use Civi\Api4\Query\Api4SelectQuery;
use Civi\Test\Api4\UnitTestCase;

/**
 * @group headless
 */
class Api4SelectQueryTest extends UnitTestCase {

  public function setUpHeadless() {
    $relatedTables = array(
      'civicrm_contact',
      'civicrm_contact',
      'civicrm_phone',
      'civicrm_option_group',
      'civicrm_option_value',
      'civicrm_activity',
      'civicrm_activity_contact',
    );
    $this->cleanup(array('tablesToTruncate' => $relatedTables));
    $this->loadDataSet('DefaultDataSet');
    $displayNameFormat = '{contact.first_name}{ }{contact.last_name}';
    \Civi::settings()->set('display_name_format', $displayNameFormat);

    return parent::setUpHeadless();
  }

  public function testBasicSelect() {
    $query = new Api4SelectQuery('Contact', FALSE);
    $results = $query->run();

    $this->assertCount(2, $results);
    $this->assertEquals('Test', array_shift($results)['first_name']);
  }

  public function testWithSingleWhereJoin() {
    $phoneNum = $this->getReference('test_phone_1')['phone'];

    $query = new Api4SelectQuery('Contact', FALSE);
    $query->where[] = array('phones.phone', '=', $phoneNum);
    $results = $query->run();

    $this->assertCount(1, $results);
  }

  public function testOneToManyJoin() {
    $phoneNum = $this->getReference('test_phone_1')['phone'];

    $query = new Api4SelectQuery('Contact', FALSE);
    $query->select[] = 'id';
    $query->select[] = 'first_name';
    $query->select[] = 'phones.phone';
    $query->where[] = array('phones.phone', '=', $phoneNum);
    $results = $query->run();

    $this->assertCount(1, $results);
    $firstResult = array_shift($results);
    $this->assertArrayHasKey('phones', $firstResult);
    $firstPhone = array_shift($firstResult['phones']);
    $this->assertEquals($phoneNum, $firstPhone['phone']);
  }

  public function testManyToOneJoin() {
    $phoneNum = $this->getReference('test_phone_1')['phone'];
    $contact = $this->getReference('test_contact_1');

    $query = new Api4SelectQuery('Phone', FALSE);
    $query->select[] = 'id';
    $query->select[] = 'phone';
    $query->select[] = 'contact.display_name';
    $query->select[] = 'contact.first_name';
    $query->where[] = array('phone', '=', $phoneNum);
    $results = $query->run();

    $this->assertCount(1, $results);
    $firstResult = array_shift($results);
    $this->assertArrayHasKey('contact', $firstResult);
    $resultContact = $firstResult['contact'];
    $this->assertEquals($contact['display_name'], $resultContact['display_name']);
  }
}
