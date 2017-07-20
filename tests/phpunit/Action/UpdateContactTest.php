<?php

namespace Civi\Test\Api4\Action;

use Civi\Test\Api4\UnitTestCase;

/**
 * @group headless
 */
class UpdateContactTest extends UnitTestCase {

  public function testUpdateWillWork() {
    $contactApi = \Civi::container()->get('contact.api');
    $contactId = $contactApi->request('create', array(
      'first_name' => 'Johann',
      'last_name' => 'Tester',
      'contact_type' => 'Individual'
    ), FALSE)['id'];

    $contact = $contactApi->request('create', array(
      'id' => $contactId,
      'first_name' => 'Testy',
    ), FALSE);

    $this->assertEquals('Testy', $contact['first_name']);
  }
}
