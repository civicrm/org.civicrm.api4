<?php
namespace Civi\API\V4\V4;

use Civi\API\V4\Entity\BaseEntity;
use Civi\API\V4\UnitTestCase;

/**
 * @group headless
 */
class EntityTest extends UnitTestCase  {

  public function testEntityGet() {
    $result = BaseEntity::get()
      ->setCheckPermissions(FALSE)
      ->execute();
    $this->assertContains('Entity', $result,
      "Entity::get missing itself");
    $this->assertContains('Participant', $result,
      "Entity::get missing Participant");
  }

  public function testEntity() {
    $result = BaseEntity::getActions()
      ->setCheckPermissions(FALSE)
      ->execute()
      ->indexBy('name');
    $this->assertEquals(
      array('get', 'getActions'),
      array_keys((array)$result),
      "Entity entity has more that basic actions");
  }

}
