<?php

namespace phpunit\Entity;

use Civi\Test\Api4\UnitTestCase;

/**
 * @group headless
 */
class ContactJoinTest extends UnitTestCase {

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

  public function testContactJoin() {
    $contact = $this->getReference('test_contact_1');
    foreach (array('Address', 'Email', 'Phone', 'OpenID', 'IM', 'Website') as $entity) {
      $result = civicrm_api4($entity, 'get', array(
        'where' => array(array('contact_id', '=', $contact['id'])),
        'select' => array('contact.display_name', 'contact.id'),
        'limit' => 1,
      ))->first();
      $this->assertEquals($contact['id'], $result['contact']['id']);
      $this->assertEquals($contact['display_name'], $result['contact']['display_name']);
    }
  }
}
