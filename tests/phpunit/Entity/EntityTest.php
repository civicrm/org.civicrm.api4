<?php

namespace Civi\Test\Api4\Entity;

use Civi\Test\Api4\UnitTestCase;

/**
 * @group headless
 */
class EntityTest extends UnitTestCase  {

  public function testEntityGet() {
    $entityApi = \Civi::container()->get('entity.api');
    $result = $entityApi->request('get', NULL, FALSE)->getArrayCopy();

    $this->assertContains('Entity', $result,
      "Entity::get missing itself");
    $this->assertContains('Participant', $result,
      "Entity::get missing Participant");
  }

  public function testEntityWillHaveOnlyBasicActions() {
    $entityApi = \Civi::container()->get('entity.api');
    $result = $entityApi->request('getActions', NULL, FALSE)->getArrayCopy();

    $expected = array('get', 'getActions');
    sort($result);
    sort($expected);

    $this->assertEquals($expected, $result);
  }

}
