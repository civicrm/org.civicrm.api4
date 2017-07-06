<?php

namespace phpunit\Query;

use Civi\API\V4\Query\Api4SelectQuery;
use Civi\Test\API\V4\UnitTestCase;

class OptionValueJoinTest extends UnitTestCase {

  public function setUpHeadless() {
    $relatedTables = array(
      'civicrm_contact',
      'civicrm_option_group',
      'civicrm_option_value',
    );

    $this->cleanup(array('tablesToTruncate' => $relatedTables));
    $this->loadDataSet('SingleContact');

    return parent::setUpHeadless();
  }

  public function testCommunicationMethodJoin() {
    $query = new Api4SelectQuery('Contact', false);
    $query->select[] = 'first_name';
    $query->select[] = 'preferred_communication_method.label';
    $results = $query->run();

    $this->assertEquals(
      'Phone',
      $results[0]['preferred_communication_method']['label']
    );
  }
}
