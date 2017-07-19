<?php

namespace Civi\Test\Api4\Entity;

use Civi\Test\Api4\UnitTestCase;

/**
 * @group headless
 */
class EntityTest extends UnitTestCase  {

  public function testEntityGet() {
    $entityApi = \Civi::container()->get('entity.api');
    $result = $entityApi->request('get');

    $this->assertContains('Entity', $result,
      "Entity::get missing itself");
    $this->assertContains('Participant', $result,
      "Entity::get missing Participant");
  }

  public function testEntity() {
    $entityApi = \Civi::container()->get('entity.api');
    $result = $entityApi->request('getActions');

    $this->assertEquals(
      array('get', 'getActions'),
      (array) $result,
      "Entity entity has more that basic actions"
    );
  }

}
