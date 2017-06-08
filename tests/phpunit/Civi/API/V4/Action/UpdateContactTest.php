<?php

namespace phpunit\Civi\API\V4\Action;

use Civi\API\V4\UnitTestCase;
use Civi\Api4\Contact;

class UpdateContactTest extends UnitTestCase {

  public function testUpdateWillWork() {
    $contactId = Contact::create()
      ->setCheckPermissions(FALSE)
      ->setValue('first_name', 'Johann')
      ->setValue('last_name', 'Tester')
      ->setValue('contact_type', 'Individual')
      ->execute()
      ->getArrayCopy()['id'];

    $contact = Contact::create()
      ->setCheckPermissions(FALSE)
      ->setValue('id', $contactId)
      ->setValue('first_name', 'Testy')
      ->execute()
      ->getArrayCopy();

    $this->assertEquals('Testy', $contact['first_name']);
  }
}
