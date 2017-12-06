<?php

namespace phpunit\Query;

use Civi\Api4\Query\Api4SelectQuery;
use Civi\Test\Api4\UnitTestCase;

/**
 * @group headless
 */
class OptionValueJoinTest extends UnitTestCase {

  public function setUpHeadless() {
    $relatedTables = array(
      'civicrm_contact',
      'civicrm_address',
      'civicrm_email',
      'civicrm_phone',
      'civicrm_openid',
      'civicrm_im',
      'civicrm_website',
      'civicrm_option_group',
      'civicrm_option_value',
      'civicrm_activity',
      'civicrm_activity_contact',
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
