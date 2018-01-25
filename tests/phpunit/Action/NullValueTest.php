<?php

namespace Civi\Test\Api4\Action;

use Civi\Api4\Contact;
use Civi\Test\Api4\UnitTestCase;

/**
 * @group headless
 */
class NullValueTest extends UnitTestCase {

  public function setUpHeadless() {
    $format = '{contact.first_name}{ }{contact.last_name}';
    \Civi::settings()->set('display_name_format', $format);
    return parent::setUpHeadless();
  }

  public function testStringNull() {
    $contact = Contact::create()
      ->setCheckPermissions(FALSE)
      ->setValue('first_name', 'Joseph')
      ->setValue('last_name', 'null')
      ->setValue('contact_type', 'Individual')
      ->execute();

    $this->assertSame('Null', $contact['last_name']);
    $this->assertSame('Joseph Null', $contact['display_name']);
  }

  public function testSettingToNull() {
    $contact = Contact::create()
      ->setCheckPermissions(FALSE)
      ->setValue('first_name', 'ILoveMy')
      ->setValue('last_name', 'LastName')
      ->setValue('contact_type', 'Individual')
      ->execute();

    $this->assertSame('ILoveMy LastName', $contact['display_name']);
    $contactId = $contact['id'];

    $contact = Contact::update()
      ->setCheckPermissions(FALSE)
      ->addWhere('id', '=', $contactId)
      ->setValue('last_name', NULL)
      ->execute()
      ->first();

    $this->assertSame(NULL, $contact['last_name']);
    $this->assertSame('ILoveMy', $contact['display_name']);
  }
}
