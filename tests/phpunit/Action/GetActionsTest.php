<?php

namespace Civi\Test\Api4\Action;

use Civi\Test\Api4\UnitTestCase;

/**
 * @group headless
 */
class GetActionsTest extends UnitTestCase {
  public function testGetActions() {
    $result = Contact::getActions()->execute();
    $names = array_column($result->getArrayCopy(), 'name');
    $basicActions = array('get', 'create', 'delete');
    $this->assertEmpty(array_diff($basicActions, $names));
  }
}
