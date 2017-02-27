<?php
namespace Civi\API\V4;
// fixme - what am I doing wrong to need this line?
require_once 'UnitTestCase.php';
use Civi\Api4\Participant;
use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;

/**
 * @group headless
 */
class EntityTest extends UnitTestCase  {

  public function testEntityGet() {
    $result = Entity::get()
      ->setCheckPermissions(FALSE)
      ->execute();
    $this->assertContains('Entity', $result,
      "Entity::get missing itself");
    $this->assertContains('Participant', $result,
      "Entity::get missing Participant");
  }

  public function testEntity() {
    $result = Entity::getActions()
      ->setCheckPermissions(FALSE)
      ->execute()
      ->indexBy('name');
    $this->assertEquals(
      array('get', 'getActions'),
      array_keys((array)$result),
      "Entity entity has more that basic actions");
  }

}
