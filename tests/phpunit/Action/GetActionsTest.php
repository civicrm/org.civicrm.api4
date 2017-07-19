<?php

namespace Civi\Test\Api4\Action;

use Civi\Test\Api4\UnitTestCase;

/**
 * @group headless
 */
class GetActionsTest extends UnitTestCase {

  public function testGetActions() {
    $contactApi = \Civi::container()->get('contact.api');
    $result = $contactApi->request('getActions')->getArrayCopy();
    $basicActions = array('get', 'create', 'delete', 'getActions');
    $this->assertEmpty(array_diff($basicActions, $result));
  }
}
