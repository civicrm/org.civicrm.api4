<?php

namespace Civi\Test\Api4\Action;

use Civi\Test\Api4\UnitTestCase;

/**
 * @group headless
 */
class GetExtraFieldsTest extends UnitTestCase {

  public function testBAOFieldsWillBeReturned() {
    $contactApi = \Civi::container()->get('contact.api');
    $returnedFields = $contactApi->request('getFields')->getArrayCopy();

    $baseFields = \CRM_Contact_BAO_Contact::fields();
    $baseFieldNames = array_column($baseFields, 'name');
    $returnedFieldNames = array_column($returnedFields, 'name');
    $notReturned = array_diff($baseFieldNames, $returnedFieldNames);

    $this->assertEmpty($notReturned);
  }

  public function testExtraFieldsWillBeAddedFromSpec() {
    $contactApi = \Civi::container()->get('contact.api');
    $returnedFields = $contactApi->request('getFields', array(
      'action' => 'create'
    ), FALSE)->getArrayCopy();

    $returnedFieldNames = array_column($returnedFields, 'name');

    $this->assertContains('dupe_check', $returnedFieldNames);
  }
}
