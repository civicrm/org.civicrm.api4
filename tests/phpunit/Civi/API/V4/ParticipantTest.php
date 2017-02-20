<?php
namespace Civi\API\V4;
// fixme - what am I doing wrong to need this line?
require 'UnitTestCase.php';
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
    parent::tearDown();
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
  }

  public function testGetActions() {
    $result = Participant::getActions()
      ->setCheckPermissions(FALSE)
      ->execute()
      ->indexBy('name');

    // fixme why is this failing?
    $this->assertEquals(FALSE, $result['get']['params']['checkPermissions']['default']);
    $this->assertEquals('Array of conditions keyed by field.', $result['get']['params']['where']['description']);
  }

  public function testGet() {

    $sql_count = $this->countTable('civicrm_participant');
    $this->assertEquals(0, $sql_count,
      "baseline count using SQL shows records still in table");

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

    // Create some test related records before proceeding
    // (5 contacts to register with 2 events)
    $contacts = $this->createEntity(array(
      'type' => 'Individual',
      'count' => 5,
      'seq' => 1));
    $events = $this->createEntity(array(
      'type' => 'Event',
      'count' => 2,
      'seq' => 1));
    // - create participants record
    foreach ($contacts as $i => $contact) {
      $participants[$i] = $this->sample(array(
        'type' => 'Participant',
        'overrides' => array(
          'event_id' => $events[$i % 2]['id'],
          'contact_id' => $contact['id'],
      )))['sample_params'];
      $create_result = Participant::create()
        ->setValues($participants[$i])
        ->setCheckPermissions(FALSE)
        ->execute();
    }
    $sql_count = $this->countTable('civicrm_participant');
    $this->assertEquals(5, $sql_count,
      "count using SQL shows records not created");

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

    // - retrieve a participant record
    // - update some records
    $patch_record = array(
      'source' => "not " . $firstResult['source'],
    );
    $first_event_id = $events[0]['id'];
    $call = Participant::update()
      ->addWhere('event_id', '=', $first_event_id)
      ->setLimit(20)
      ->setValues($patch_record)
      ->setCheckPermissions(FALSE)
      ->execute();
      \Civi::log()->debug('$call: '.json_encode($call,JSON_PRETTY_PRINT));

    // - delete some records
    $second_event_id = $events[1]['id'];
    $delete_result = Participant::delete()
      ->addWhere('event_id', '=', $second_event_id)
      ->setCheckPermissions(FALSE)
      ->execute();
    $this->assertEquals(array(2,4), (array)$delete_result,
      "didn't delete every second record as expected");

    $sql_count = $this->countTable('civicrm_participant');
    $this->assertEquals(3, $sql_count,
      "records not gone from database after delete");

    // $this->markTestIncomplete();
  }

}
