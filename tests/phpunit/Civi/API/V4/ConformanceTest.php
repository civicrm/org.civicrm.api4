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
 */
class ConformanceTest extends UnitTestCase {

  /**
   * Set up baseline for testing
   */
  public function setUp() {
    parent::setUp();
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   *
   * This method is called after a test is executed.
   */
  public function tearDown() {
    parent::tearDown();
  }

  protected function report($string) {
    echo $string . "\n";
  }

  public function testConformance() {
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
        $create_result = $entity_class::create()
          ->setValues($dummy)
          ->setCheckPermissions(FALSE)
          ->execute();
        $this->assertArrayHasKey('id', $create_result, "create missing ID");
        $id = $create_result['id'];
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
