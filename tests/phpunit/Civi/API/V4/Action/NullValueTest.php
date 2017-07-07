<?php

namespace phpunit\Civi\API\V4\Action;

use Civi\API\V4\UnitTestCase;
use Civi\Api4\Contact;

/**
 * @group headless
 */
class NullValueTest extends UnitTestCase {

  public function testStringNull() {

    \Civi::settings()->set(
      'display_name_format',
      '{contact.first_name}{ }{contact.last_name}'
    );

    $contact = Contact::create()
      ->setCheckPermissions(FALSE)
      ->setValue('first_name', 'Joseph')
      ->setValue('last_name', 'null')
      ->setValue('contact_type', 'Individual')
      ->execute();

    $this->assertSame('Null', $contact['last_name']);
    $this->assertSame('Joseph Null', $contact['display_name']);
  }

  public function testSettingToNullA() {
    $contactId = Contact::create()
      ->setCheckPermissions(FALSE)
      ->setValue('first_name', 'ILoveMy')
      ->setValue('last_name', 'LastName')
      ->setValue('contact_type', 'Individual')
      ->execute()['id'];

    $contact = Contact::create()
      ->setCheckPermissions(FALSE)
      ->setValue('id', $contactId)
      ->setValue('last_name', NULL)
      ->execute();

    $this->assertSame(NULL, $contact['last_name']);
    $this->assertSame('ILoveMy', $contact['display_name']);
  }
}
