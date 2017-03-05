<?php
namespace Civi\API\V4;
// fixme - what am I doing wrong to need this line?
require_once 'UnitTestCase.php';
use Civi\Api4\Participant;
use Civi\Api4\Contact;
use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;

/**
 * @group headless
 *
 * This uses some hook kernel set-up copied from
 *   tests/phpunit/CiviTest/CiviUnitTestCase.php
 */
class ConformanceTest extends UnitTestCase {

  private $hook_calls = array();

  /**
   * Set up baseline for testing
   */
  public function setUp() {
    parent::setUp();
    $this->hookClass = \CRM_Utils_Hook::singleton();
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   *
   * This method is called after a test is executed.
   */
  public function tearDown() {
    parent::tearDown();
    // \CRM_Utils_Hook::singleton()->reset(); << -- is this actually needed
    $this->hookClass->reset();
  }

  /**
   * Temporary bodge to help with debugging
   * @param string $string to report
   */
  protected function report($string) {
    echo $string . "\n";
  }

  /**
   * Reset the hook log
   */
  protected function resetHookLog() {
    $this->hook_calls = array();
  }

  /**
   * Provide a catch method to snoop on hook calls
   * @param string $name hook being invoked
   * @param array $arguments hook paramters
   */
  public function __call($name, $arguments) {
    $this->hook_calls[$name] = 1
      + (isset($this->hook_calls[$name]) ? $this->hook_calls[$name] : 0);
  }

  public function testConformance() {
    $this->hookClass->setMock($this);
    // get list of all the entities we know about and loop over them:
    $entities = Entity::get()
      ->setCheckPermissions(FALSE)
      ->execute();
    foreach ($entities as $entity) {
      $entity_class = 'Civi\Api4\\' . $entity;
      $this->report("## Testing $entity");
      $actions = $entity_class::getActions()
        ->setCheckPermissions(FALSE)
        ->execute()
        ->indexBy('name');
      $this->report("Actions: \n" . json_encode(
        array_keys((array) $actions), JSON_PRETTY_PRINT));

      if ($entity != 'Entity') {
        // fields
        $fields = $entity_class::getFields()
          ->setCheckPermissions(FALSE)
          ->execute()
          ->indexBy('name');
        $this->report("Fields: \n" . json_encode(
          (array) $fields, JSON_PRETTY_PRINT));
        $this->assertArraySubset(
          array('type' => 1, 'required' => TRUE),
          $fields['id'],
          "$entity fields missing required ID field of proper type");
        $this->assertArraySubset(
          array('type' => 1, 'required' => TRUE),
          $fields['id'],
          "$entity fields missing required ID field of proper type");
        // create
        $dummy = $this->sample(array('type' => $entity))['sample_params'];
        $this->resetHookLog();
        $this->report("Hook calls: \n" . json_encode($this->hook_calls, JSON_PRETTY_PRINT));
        $create_result = $entity_class::create()
          ->setValues($dummy)
          ->setCheckPermissions(FALSE)
          ->execute();
        $this->assertArrayHasKey('id', $create_result, "create missing ID");
        $id = $create_result['id'];
        $this->assertGreaterThanOrEqual(4, count($this->hook_calls), "$entity create not evoke enough hooks");
        $this->report("Hook calls: \n" . json_encode($this->hook_calls, JSON_PRETTY_PRINT));
        $this->assertGreaterThanOrEqual(1, $id, "$entity ID not positive");

        // retrieve
        $get_result = $entity_class::get()
          ->setCheckPermissions(FALSE)
          ->addClause(array('id', '=', $id))
          ->execute();
        $this->assertEquals(1, count($get_result),
          "failed to get single fresh $entity");
        // update

        // delete
        $delete_result = $entity_class::delete()
          ->setCheckPermissions(FALSE)
          ->addClause(array('id', '=', $id))
          ->execute();
        // should get back an array of deleted id:
        $this->assertEquals(array($id), (array) $delete_result,
          "unexpected delete result from $entity");
        $get_deleted_result = $entity_class::get()
          ->setCheckPermissions(FALSE)
          ->addClause(array('id', '=', $id))
          ->execute();
        $this->assertEquals(0, count($get_deleted_result),
          "still getting back data after delete of $entity");
      }
    }
  }

}
