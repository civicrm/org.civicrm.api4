<?php

namespace Civi\Test\API\V4\Action;

use Civi\Test\API\V4\UnitTestCase;
use Civi\API\V4\Entity\Contact;

class GetExtraFieldsTest extends UnitTestCase {

  public function testBAOFieldsWillBeReturned() {
    $returnedFields = Contact::getFields()
      ->execute()
      ->getArrayCopy();

    $baseFields = \CRM_Contact_BAO_Contact::fields();
    $baseFieldNames = array_column($baseFields, 'name');
    $returnedFieldNames = array_column($returnedFields, 'name');
    $notReturned = array_diff($baseFieldNames, $returnedFieldNames);

    $this->assertEmpty($notReturned);
  }

  public function testExtraFieldsWillBeAddedFromSpec() {
    $returnedFields = Contact::getFields()
      ->setAction('create')
      ->execute()
      ->getArrayCopy();

    $returnedFieldNames = array_column($returnedFields, 'name');

    $this->assertContains('dupe_check', $returnedFieldNames);
  }

  public function testCustomFieldsWillBeAdded() {
    // todo
  }
}