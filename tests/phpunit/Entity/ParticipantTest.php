<?php

namespace Civi\Test\Api4\Entity;

use Civi\Api4\GetParameterBag;
use Civi\Test\Api4\UnitTestCase;

/**
 * @group headless
 */
class ParticipantTest extends UnitTestCase  {

  public function setUp() {
    parent::setUp();
    $truncateTables = array(
      'civicrm_participant',
      'civicrm_option_group',
      'civicrm_option_value',
      'civicrm_location_type'
    );
    $this->dropTables(array('tablesToTruncate' => $truncateTables));
    $this->loadDataSet('ParticipantRoleOptionGroup');
    $this->loadDataSet('LocationTypes');
  }

  public function testGet() {
    if ($this->getRowCount('civicrm_participant') > 0) {
      $this->markTestSkipped('Participant table must be empty');
    }

    $participantApi = \Civi::container()->get('participant.api');

    // With no records:
    $result = $participantApi->request('get', NULL, FALSE);
    $this->assertEquals(0, $result->count(), "count of empty get is not 0");

    // Create some test related records before proceeding
    $participantCount = 20;
    $contactCount = 7;
    $eventCount = 5;

    // All events will either have this number or one less because of the
    // rotating participation creation method.
    $expectedFirstEventCount = ceil($participantCount / $eventCount);

    $dummy = array(
      'contacts' => $this->createEntity(array(
        'type' => 'Individual',
        'count' => $contactCount,
        'seq' => 1)),
      'events' => $this->createEntity(array(
        'type' => 'Event',
        'count' => $eventCount,
        'seq' => 1)),
      'sources' => array('Paddington', 'Springfield', 'Central'),
    );

    // - create dummy participants record
    for ($i = 0; $i < $participantCount; $i++) {
      $dummy['participants'][$i] = $this->sample(array(
        'type' => 'Participant',
        'overrides' => array(
          'event_id' => $dummy['events'][$i % $eventCount]['id'],
          'contact_id' => $dummy['contacts'][$i % $contactCount]['id'],
          'source' => $dummy['sources'][$i % 3], // 3 = number of sources
      )))['sample_params'];

       $participantApi->request('create', $dummy['participants'][$i], FALSE);
    }
    $sqlCount = $this->getRowCount('civicrm_participant');
    $this->assertEquals($participantCount, $sqlCount, "Unexpected count");

    $firstEventId = $dummy['events'][0]['id'];
    $secondEventId = $dummy['events'][1]['id'];
    $firstContactId = $dummy['contacts'][0]['id'];

    $params = new GetParameterBag();
    $params->addClause(array('event_id', '=', $firstEventId));
    $firstOnlyResult = $participantApi->request('get', $params, FALSE);

    $this->assertEquals($expectedFirstEventCount, count($firstOnlyResult),
      "count of first event is not $expectedFirstEventCount");

    // get first two events using different methods

    $params = new GetParameterBag();
    $params->addWhere('event_id', 'IN', array($firstEventId, $secondEventId));
    $firstTwo = $participantApi->request('get', $params, FALSE);

    $firstResult = $result->first();

    // verify counts
    // count should either twice the first event count or one less
    $this->assertLessThanOrEqual(
      $expectedFirstEventCount * 2,
      count($firstTwo),
      "count is too high"
    );

    $this->assertGreaterThanOrEqual(
      $expectedFirstEventCount * 2 - 1,
      count($firstTwo),
      "count is too low"
    );

    $params = new GetParameterBag();
    $params->addWhere('event_id', '=', $firstEventId);
    $params->addWhere('contact_id', '=', $firstContactId);
    $firstParticipantResult = $participantApi->request('get', $params, FALSE);

    $this->assertEquals(1, count($firstParticipantResult), "more than one registration");

    $firstParticipantId = $firstParticipantResult->first()['id'];

    // get a result which excludes $first_participant
    $params = new GetParameterBag();
    $params->addSelect('id');
    $params->addClause(
      array('NOT',
        array('AND',
          array(
            array('event_id', '=', $firstEventId),
            array('contact_id', '=', $firstContactId)
          )
        )
      )
    );
    $otherParticipantResult = $participantApi->request('get', $params, FALSE);
    $otherParticipantResult->indexBy('id');

    $this->assertEquals($participantCount - 1,
      count($otherParticipantResult),
      "failed to exclude a single record on complex criteria");
    // check the record we have excluded is the right one:

    $this->assertFalse(
      $otherParticipantResult->offsetExists($firstParticipantId),
      'excluded wrong record');

    // retrieve a participant record and update some records
    $params = new GetParameterBag();
    $params->addWhere('event_id', '=', $firstEventId);
    $params->setLimit(20);
    $params->set('source', "not " . $firstResult['source']);
    $participantApi->request('update', $params, FALSE);

    // - delete some records
    $secondEventId = $dummy['events'][1]['id'];
    $params = new GetParameterBag();
    $params->addWhere('event_id', '=', $secondEventId);
    $deleteResult = $participantApi->request('delete', $params, FALSE);

    $expectedDeletes = array(2,7,12,17);
    $this->assertEquals($expectedDeletes, (array)$deleteResult,
      "didn't delete every second record as expected");

    $sqlCount = $this->getRowCount('civicrm_participant');
    $this->assertEquals(
      $participantCount - count($expectedDeletes),
      $sqlCount,
      "records not gone from database after delete");
  }
}
