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
//class ParticipantTest extends \PHPUnit_Framework_TestCase implements HeadlessInterface, TransactionalInterface {
class ParticipantTest extends UnitTestCase  {

 /**
   * Set up baseline for testing
   */
  public function setUp() {
    parent::setUp();
    $cleanup_params = array(
      'tablesToTruncate' => array(
        'civicrm_participant',
      ),
    );
    $this->cleanup($cleanup_params);
  }

 /**
   * Tears down the fixture, for example, closes a network connection.
   *
   * This method is called after a test is executed.
   */
  public function tearDown() {
    parent::tearDown();
  }

  public function testGetActions() {
    $result = Participant::getActions()
      ->setCheckPermissions(FALSE)
      ->execute()
      ->indexBy('name');

    // fixme - this should be FALSE ???
    $this->assertEquals(TRUE, $result['get']['params']['checkPermissions']['default']);
    $this->assertEquals('Array of conditions keyed by field.', $result['get']['params']['where']['description']);
  }

  public function testGet() {

    $sql_count = $this->countTable('civicrm_participant');
    $this->assertEquals(0, $sql_count,
      "baseline count using SQL shows records still in table");

    /////////////////////////////////////////// empty result
    // test behaviour with no records:
    $call = Participant::get()
      ->setCheckPermissions(FALSE)
      ->setLimit(5);
    $empty_result = $call->execute();
    $this->assertEquals(0, count($empty_result),
      "count of empty get is not 0");

    // Check that the $empty_result arrayObject knows what the inputs were
    $this->assertEquals('Participant', $empty_result->entity);
    $this->assertEquals('get', $empty_result->action);

    // Result object ought to know what version of the api we are using
    $this->assertEquals(4, $empty_result->version);

    // @todo test these:
    //$paramInfo = Participant::get()->getParamInfo();
    //    $paramInfo = Participant::get()->getParams();
    //    $paramInfo = $call->getParams();

    /////////////////////////////////////////// make dummy data
    // Create some test related records before proceeding
    $participant_count = 20;
    $contact_count = 7;
    $event_count = 5;
    // How many events in first event?
    // All events will either have this number or one less because of the
    // rotating participation creation method.
    $expected_first_event_count = ceil($participant_count / $event_count);

    $dummy = array(
      'contacts' => $this->createEntity(array(
        'type' => 'Individual',
        'count' => $contact_count,
        'seq' => 1)),
      'events' => $this->createEntity(array(
        'type' => 'Event',
        'count' => $event_count,
        'seq' => 1)),
      'sources' => array('Paddington', 'Springfield', 'Central'),
    );
    // - create dummy participants record
    for ($i = 0; $i < $participant_count; $i++) {
      $dummy['participants'][$i] = $this->sample(array(
        'type' => 'Participant',
        'overrides' => array(
          'event_id' => $dummy['events'][$i % $event_count]['id'],
          'contact_id' => $dummy['contacts'][$i % $contact_count]['id'],
          'source' => $dummy['sources'][$i % 3], // 3 = number of sources
      )))['sample_params'];
      $create_result = Participant::create()
        ->setValues($dummy['participants'][$i])
        ->setCheckPermissions(FALSE)
        ->execute();
    }
    $sql_count = $this->countTable('civicrm_participant');
    $this->assertEquals($participant_count, $sql_count,
      "count using SQL shows records not created");

    /////////////////////////////////////////// simple get
    $call = Participant::get()
      ->setCheckPermissions(FALSE)
      ->setLimit(2);
    $result = $call->execute();
    $this->assertEquals(2, count($result),
      "did not get back two records as expected");

    // fixme - this is a bit brittle?
    $firstResult = $result->first();
    $this->assertEquals(1, $firstResult['id']);

    // By default the $result arrayObject should be non-associative
    $this->assertEquals([1, 2], array_keys((array)$result));

    // test indexBy():
    $result->indexBy('id');
    // Array should still contain 2 items after re-index
    $this->assertEquals(2, count($result));

    // All values should now be keyed by id
    foreach ($result as $key => $values) {
      $this->assertEquals($values['id'], $key);
    }

    $first_event_id = $dummy['events'][0]['id'];
    $second_event_id = $dummy['events'][1]['id'];
    $first_contact_id = $dummy['contacts'][0]['id'];
    $first_source = $dummy['sources'][0];

    $first_only_result = Participant::get()
      ->setCheckPermissions(FALSE)
      ->addClause(array('event_id', '=', $first_event_id))
      ->execute();

    $this->assertEquals($expected_first_event_count, count($first_only_result),
      "count of first event is not $expected_first_event_count");

    /////////////////////////////////////////// simple Boolean
    // get first two events using different methods
    $first_two_with_addWhere = Participant::get()
      ->setCheckPermissions(FALSE)
      ->addWhere('event_id', 'IN', array($first_event_id, $second_event_id))
      ->execute();
    $first_two_with_addClause = Participant::get()
      ->setCheckPermissions(FALSE)
      ->addClause(array('event_id', 'IN', array($first_event_id, $second_event_id)))
      ->execute();
    $first_two_with_or = Participant::get()
      ->setCheckPermissions(FALSE)
      ->addClause(array('OR', array(
        array('event_id', '=', $first_event_id),
        array('event_id', '=', $second_event_id))
      ))
      ->execute();
    // verify counts ///
    // count should either twice the first event count or one less
    $this->assertLessThanOrEqual(
      $expected_first_event_count * 2,
      count($first_two_with_addWhere),
      "first_two_with_addWhere is too high");
    $this->assertGreaterThanOrEqual(
      $expected_first_event_count * 2 -1,
      count($first_two_with_addWhere),
      "first_two_with_addWhere is too low");
    // todo should probably get the id list and check they match
    $this->assertEquals(
      count($first_two_with_addClause),
      count($first_two_with_addWhere),
      "addWhere and addClause produce different counts");
    $this->assertEquals(
      count($first_two_with_addClause),
      count($first_two_with_or),
      "addWhere and addClause produce different counts");

    $first_participant_result = Participant::get()
      ->setCheckPermissions(FALSE)
      ->addWhere('event_id', '=', $first_event_id)
      ->addWhere('contact_id', '=', $first_contact_id)
      ->execute();
    $this->assertEquals(1, count($first_participant_result),
      "more than one registration");
    $first_participant = $first_participant_result->first()['id'];
    // get a result which excludes $first_participant
    // using 2 different approaches
    $not_first_participant_result = Participant::get()
      ->setCheckPermissions(FALSE)
      ->setSelect(['id'])
      ->addClause(array('NOT',
        array('AND', array(
          array('event_id', '=', $first_event_id),
          array('contact_id', '=', $first_contact_id)))))
      ->execute()
      ->indexBy('id');
    $this->assertEquals($participant_count - 1,
      count($not_first_participant_result),
      "failed to exclude a single record on complex criteria");
    // checke the record we have excluded is the right one:
    $this->assertFalse(
      $not_first_participant_result->offsetExists($first_participant),
      'excluded wrong record');
    $not_first_participant_result_via_or = Participant::get()
      ->setCheckPermissions(FALSE)
      ->setSelect(['id'])
      ->addClause(array('OR', array(
          array('event_id', '!=', $first_event_id),
          array('contact_id', '!=', $first_contact_id))))
      ->execute()
      ->indexBy('id');
    $this->assertEquals(
      $not_first_participant_result,
      $not_first_participant_result_via_or,
      "logical mismatch");

    /////////////////////////////////////////// patching
    // - retrieve a participant record
    // - update some records
    $patch_record = array(
      'source' => "not " . $firstResult['source'],
    );
    $call = Participant::update()
      ->addWhere('event_id', '=', $first_event_id)
      ->setCheckPermissions(FALSE)
      ->setLimit(20)
      ->setValues($patch_record)
      ->setCheckPermissions(FALSE)
      ->execute();

    // - delete some records
    $second_event_id = $dummy['events'][1]['id'];
    $delete_result = Participant::delete()
      ->addWhere('event_id', '=', $second_event_id)
      ->setCheckPermissions(FALSE)
      ->execute();
    $expected_deletes = array(2,7,12,17);
    $this->assertEquals($expected_deletes, (array)$delete_result,
      "didn't delete every second record as expected");

    $sql_count = $this->countTable('civicrm_participant');
    $this->assertEquals($participant_count - count($expected_deletes), $sql_count,
      "records not gone from database after delete");

    // $this->markTestIncomplete();
  }

}
