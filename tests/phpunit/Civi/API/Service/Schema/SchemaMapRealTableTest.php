<?php

namespace phpunit\Civi\API\Service\Schema;

use Civi\API\V4\UnitTestCase;

/**
 * @group headless
 */
class SchemaMapRealTableTest extends UnitTestCase {
  public function testAutoloadWillPopulateTablesByDefault() {
    $map = \Civi::container()->get('schema_map');
    $this->assertNotEmpty($map->getTables());
  }

  public function testPathWillExist() {
    $map = \Civi::container()->get('schema_map');
    $path = $map->getPath('civicrm_contact', 'email');
    $this->assertCount(2, $path);
  }
}
