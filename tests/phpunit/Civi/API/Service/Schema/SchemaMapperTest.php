<?php

namespace phpunit\Civi\API\Service\Schema;

use Civi\API\Service\Schema\Joinable;
use Civi\API\Service\Schema\SchemaMap;
use Civi\API\Service\Schema\Table;
use Civi\API\V4\UnitTestCase;

/**
 * @group headless
 */
class SchemaMapperTest extends UnitTestCase {

  public function testWillHaveNoPathWithNoTables() {
    $map = new SchemaMap();
    $this->assertEmpty($map->getPath('foo', 'bar'));
  }

  public function testWillHavePathWithSingleJump() {
    $map = new SchemaMap();

    $phoneTable = new Table('civicrm_phone', 'phone');
    $locationTable = new Table('civicrm_location_type', 'location');

    $link = new Joinable('civicrm_location_type', 'id');
    $phoneTable->addTableLink('location_type_id', $link);

    $map->addTables(array($phoneTable, $locationTable));

    $this->assertNotEmpty($map->getPath('civicrm_phone', 'civicrm_location_type'));
  }

  public function testWillHavePathWithDoubleJump() {
    $map = new SchemaMap();

    $activityTable = new Table('activity');
    $activityContactTable = new Table('activity_contact');

    $middleLink = new Joinable('activity_contact', 'activity_id');
    $activityTable->addTableLink('id', $middleLink);

    $contactLink = new Joinable('contact', 'id');
    $activityContactTable->addTableLink('contact_id', $contactLink);

    $map->addTables(array($activityTable, $activityContactTable));

    $this->assertNotEmpty($map->getPath('activity', 'contact'));
  }

  public function testCircularReferenceWillNotBreakIt() {
    $map = new SchemaMap();

    $contactTable = new Table('contact');
    $carTable = new Table('car');

    $carLink = new Joinable('car', 'id');
    $contactTable->addTableLink('car_id', $carLink);

    $ownerLink = new Joinable('contact', 'id');
    $carTable->addTableLink('owner_id', $ownerLink);

    $map->addTables(array($contactTable, $carTable));

    $this->assertEmpty($map->getPath('contact', 'foo'));
  }

  public function testCannotGoOverJoinLimit() {
    $map = new SchemaMap();

    $first = new Table('first');
    $second = new Table('second');
    $third = new Table('third');
    $fourth = new Table('fourth');
    $first->addTableLink('id', new Joinable('second', 'id'));
    $second->addTableLink('id', new Joinable('third', 'id'));
    $third->addTableLink('id', new Joinable('fourth', 'id'));
    $fourth->addTableLink('id', new Joinable('fifth', 'id'));

    $map->addTables(array($first, $second, $third, $fourth));

    $this->assertEmpty($map->getPath('first', 'fifth'));
  }
}
