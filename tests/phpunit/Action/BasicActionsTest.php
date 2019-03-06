<?php

namespace Civi\Test\Api4\Action;

use Civi\Test\Api4\UnitTestCase;
use Civi\Api4\MockBasicEntity;

/**
 * @group headless
 */
class BasicActionsTest extends UnitTestCase {

  public function testCrud() {
    MockBasicEntity::delete()->addWhere('id', '>', 0)->execute();

    $id1 = MockBasicEntity::create()->addValue('foo', 'one')->execute()->first()['id'];

    $result = MockBasicEntity::get()->execute();
    $this->assertCount(1, $result);

    $id2 = MockBasicEntity::create()->addValue('foo', 'two')->execute()->first()['id'];

    $result = MockBasicEntity::get()->execute();
    $this->assertCount(2, $result);

    MockBasicEntity::update()->addWhere('id', '=', $id2)->addValue('foo', 'new')->execute();

    $result = MockBasicEntity::get()->addOrderBy('id', 'DESC')->setLimit(1)->execute();
    $this->assertCount(1, $result);
    $this->assertEquals('new', $result->first()['foo']);

    MockBasicEntity::delete()->addWhere('id', '=', $id2);
    $result = MockBasicEntity::get()->execute();
    $this->assertEquals('one', $result->first()['foo']);
  }

  public function testReplace() {
    MockBasicEntity::delete()->addWhere('id', '>', 0)->execute();

    $objects = [
      ['group' => 'one', 'color' => 'red'],
      ['group' => 'one', 'color' => 'blue'],
      ['group' => 'one', 'color' => 'green'],
      ['group' => 'two', 'color' => 'orange'],
    ];

    foreach ($objects as &$object) {
      $object['id'] = MockBasicEntity::create()->setValues($object)->execute()->first()['id'];
    }

    // Keep red, change blue, delete green, and add yellow
    $replacements = [
      ['color' => 'red', 'id' => $objects[0]['id']],
      ['color' => 'not blue', 'id' => $objects[1]['id']],
      ['color' => 'yellow']
    ];

    MockBasicEntity::replace()->addWhere('group', '=', 'one')->setRecords($replacements)->execute();

    $newObjects = MockBasicEntity::get()->addOrderBy('id', 'DESC')->execute()->indexBy('id');

    $this->assertCount(4, $newObjects);

    $this->assertEquals('yellow', $newObjects->first()['color']);

    $this->assertEquals('not blue', $newObjects[$objects[1]['id']]['color']);

    // Ensure group two hasn't been altered
    $this->assertEquals('orange', $newObjects[$objects[3]['id']]['color']);
    $this->assertEquals('two', $newObjects[$objects[3]['id']]['group']);
  }

  public function testBatchFrobnicate() {
    MockBasicEntity::delete()->addWhere('id', '>', 0)->execute();

    $objects = [
      ['group' => 'one', 'color' => 'red', 'number' => 10],
      ['group' => 'one', 'color' => 'blue', 'number' => 20],
      ['group' => 'one', 'color' => 'green', 'number' => 30],
      ['group' => 'two', 'color' => 'blue', 'number' => 40],
    ];
    foreach ($objects as &$object) {
      $object['id'] = MockBasicEntity::create()->setValues($object)->execute()->first()['id'];
    }

    $result = MockBasicEntity::batchFrobnicate()->addWhere('color', '=', 'blue')->execute();
    $this->assertEquals(2, count($result));
    $this->assertEquals([400, 1600], \CRM_Utils_Array::collect('frobnication', (array) $result));
  }

}
