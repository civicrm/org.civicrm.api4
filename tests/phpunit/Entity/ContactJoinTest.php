<?php

namespace phpunit\Entity;

use Civi\Api4\Entity\Contact;
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
      $results = civicrm_api4($entity, 'get', array(
        'where' => array(array('contact_id', '=', $contact['id'])),
        'select' => array('contact.display_name', 'contact.id'),
      ));
      foreach ($results as $result) {
        $this->assertEquals($contact['id'], $result['contact']['id']);
        $this->assertEquals($contact['display_name'], $result['contact']['display_name']);
      }
    }
  }

  public function testJoinToPCM() {
    $contact = Contact::create()
      ->setValues(array("preferred_communication_method" => array(1,2,3), 'contact_type' => 'Individual', 'first_name' => 'Test', 'last_name' => 'PCM'))
      ->execute();

    $fetchedContact = Contact::get()
      ->addWhere('id', '=', $contact['id'])
      ->addSelect('preferred_communication_method.label')
      ->execute()
      ->first();

    // Todo: this test is failing due to a bug in the join code,
    // but it also needs pcm option values populated in order to pass.
    $this->assertEquals(3, count($fetchedContact["preferred_communication_method"]));
  }
}
