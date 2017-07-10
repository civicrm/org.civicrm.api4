<?php

namespace Civi\Test\Api4\Action;

use Civi\Api4\Entity\Contact;
use Civi\Test\Api4\UnitTestCase;

class UpdateContactTest extends UnitTestCase {

  public function testUpdateWillWork() {
    $contactId = Contact::create()
      ->setCheckPermissions(FALSE)
      ->setValue('first_name', 'Johann')
      ->setValue('last_name', 'Tester')
      ->setValue('contact_type', 'Individual')
      ->execute()['id'];

    $contact = Contact::create()
      ->setCheckPermissions(FALSE)
      ->setValue('id', $contactId)
      ->setValue('first_name', 'Testy')
      ->execute();

    $this->assertEquals('Testy', $contact['first_name']);
  }
}
