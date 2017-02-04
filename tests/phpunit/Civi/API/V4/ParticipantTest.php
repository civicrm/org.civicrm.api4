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
   * Tears down the fixture, for example, closes a network connection.
   *
   * This method is called after a test is executed.
   */
  public function tearDown() {
    parent::tearDown();
    $cleanup_params = array(
      'tablesToTruncate' => array(
        'civicrm_participant',
      ),
    );
    $this->cleanup($cleanup_params);
  }

  public function testGetActions() {
    $result = Participant::getActions()
      ->execute()
      ->indexBy('name');

    $this->assertEquals(FALSE, $result['get']['params']['checkPermissions']['default']);
    $this->assertEquals('Array of conditions keyed by field.', $result['get']['params']['where']['description']);
  }

  public function testGet() {
    // Api4 calls returns an arrayObject
    // @see http://php.net/manual/en/class.arrayobject.php
    // You can 'foreach' it like a normal array and it also stores "extra" properties - perfect for api metadata
    // It looks like this:
    // $result = array(
    //   1 => array('id' => 123, 'event_id' => 12, 'contact_id' => 456... etc),
    //   2 => array('id' => ... etc),
    // )->version = 4
    //  ->entity = 'Participant'
    //  ->action = 'get'

    $call = Participant::get()
      ->setLimit(5);
    $result = $call->execute();

    // Check that the $result arrayObject knows what the inputs were
    $this->assertEquals('Participant', $result->entity);
    $this->assertEquals('get', $result->action);

    // Result object ought to know what version of the api we are using
    $this->assertEquals(4, $result->version);

    // @todo test these:
    //$paramInfo = Participant::get()->getParamInfo();
    //    $paramInfo = Participant::get()->getParams();
    //    \Civi::log()->info('base params', $paramInfo);
    //    $paramInfo = $call->getParams();
    //    \Civi::log()->info('params', $paramInfo);

    //TODO: need to create some test records before proceeding
    // - flush participant table
    // - get some contacts
    //  $result = civicrm_api3('Contact', 'get', array('sequential' => 1));
    // - get an event
    // - create a participant record
    // - retrieve a participant record
    // - update some records
    // - delete some records
    $this->markTestIncomplete();

    // Here's a convenient way to get the first result - maybe a replacement for getsingle
    // Rationale for ditching getsingle - it's an output format & not a real action
    // and output transformations would be better handled by the $result object.
    $firstResult = $result->first();
    $this->assertEquals(1, $firstResult['id']);

    // By default the $result arrayObject should be non-associative
    $this->assertEquals([0, 1, 2, 3, 4], array_keys((array) $result));

    // Let's re-index by id (in v3 "sequential => 0")
    // Ditching "sequential" keeps better separation between input params and output formats
    $result->indexBy('id');
    // Array should still contain 5 items after re-index
    $this->assertEquals(5, count($result));

    // All values should now be keyed by id
    // This demonstrates how the $results object can be treated like a normal array
    // Meta properties like entity and version will not be looped
    foreach ($result as $key => $values) {
      $this->assertEquals($values['id'], $key);
    }
  }

}
