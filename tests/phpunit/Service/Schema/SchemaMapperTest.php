<?php

namespace phpunit\Civi\API\Service\Schema;

use Civi\Api4\Service\Schema\Joinable\Joinable;
use Civi\Api4\Service\Schema\SchemaMap;
use Civi\Api4\Service\Schema\Table;
use Civi\Test\Api4\UnitTestCase;

/**
 * @group headless
 */
class SchemaMapperTest extends UnitTestCase {

  public function testWillHaveNoPathWithNoTables() {
    $map = new SchemaMap();
    $this->assertEmpty($map->getPath('foo', 'bar'));
  }

  public function testWillHavePathWithSingleJump() {
    $phoneTable = new Table('civicrm_phone');
    $locationTable = new Table('civicrm_location_type');
    $link = new Joinable('civicrm_location_type', 'id', 'location');
    $phoneTable->addTableLink('location_type_id', $link);

    $map = new SchemaMap();
    $map->addTables(array($phoneTable, $locationTable));

    $this->assertNotEmpty($map->getPath('civicrm_phone', 'location'));
  }

  public function testCannotGoOverJoinLimit() {
    $activity = new Table('activity');
    $activityContact = new Table('activity_contact');
    $middleLink = new Joinable('activity_contact', 'activity_id');
    $contactLink = new Joinable('contact', 'id');
    $activity->addTableLink('id', $middleLink);
    $activityContact->addTableLink('contact_id', $contactLink);

    $map = new SchemaMap();
    $map->addTables(array($activity, $activityContact));

    $this->assertEmpty($map->getPath('activity', 'contact'));
  }

  public function testCircularReferenceWillNotBreakIt() {
    $contactTable = new Table('contact');
    $carTable = new Table('car');
    $carLink = new Joinable('car', 'id');
    $ownerLink = new Joinable('contact', 'id');
    $contactTable->addTableLink('car_id', $carLink);
    $carTable->addTableLink('owner_id', $ownerLink);

    $map = new SchemaMap();
    $map->addTables(array($contactTable, $carTable));

    $this->assertEmpty($map->getPath('contact', 'foo'));
  }
}
