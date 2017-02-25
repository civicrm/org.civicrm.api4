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
//class ParticipantTest extends \PHPUnit_Framework_TestCase implements HeadlessInterface, TransactionalInterface {
class ConformanceTest extends UnitTestCase  {

 /**
   * Set up baseline for testing
   */
  public function setUp() {
  }

 /**
   * Tears down the fixture, for example, closes a network connection.
   *
   * This method is called after a test is executed.
   */
  public function tearDown() {
  }

  protected function report($string) {
    echo $string . "\n";
  }

  public function testConformance() {
    $entities = Entity::get()
      ->setCheckPermissions(FALSE)
      ->execute();
    foreach ($entities as $entity) {
      $fullclass = 'Civi\Api4\\' . $entity;
      $this->report("## Testing $entity");
      $actions = $fullclass::getActions()
        ->setCheckPermissions(FALSE)
        ->execute()
        ->indexBy('name');
      $this->report("Actions: \n".json_encode(
        array_keys((array)$actions),JSON_PRETTY_PRINT));


      if ($entity != 'Entity') {
        $dummy = $this->sample(array('type' => $entity))['sample_params'];
        echo '$dummy: '.json_encode($dummy,JSON_PRETTY_PRINT)."\n";
        $create_result = $fullclass::create()
          ->setValues($dummy)
          ->setCheckPermissions(FALSE)
          ->execute();
        echo 'create_result: '.json_encode($create_result,JSON_PRETTY_PRINT)."\n";

      }
    }
  }

}
