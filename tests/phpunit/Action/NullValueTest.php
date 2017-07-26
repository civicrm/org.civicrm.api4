<?php

namespace Civi\Test\Api4\Action;

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
    $contactApi = \Civi::container()->get('contact.api');
    $contact = $contactApi->request('create', array(
      'first_name' => 'Joseph',
      'last_name' => 'null',
      'contact_type' => 'Individual',
    ), FALSE);

    $this->assertSame('Null', $contact['last_name']);
    $this->assertSame('Joseph Null', $contact['display_name']);
  }

  public function testSettingToNullA() {
    $contactApi = \Civi::container()->get('contact.api');
    $contactId = $contactApi->request('create', array(
      'first_name' => 'ILoveMy',
      'last_name' => 'LastName',
      'contact_type' => 'Individual',
    ), FALSE)['id'];

    $contact = $contactApi->request('create', array(
      'id' => $contactId,
      'last_name' => NULL
    ), FALSE);

    $this->assertSame(NULL, $contact['last_name']);
    $this->assertSame('ILoveMy', $contact['display_name']);
  }
}
