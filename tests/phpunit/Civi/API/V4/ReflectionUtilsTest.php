<?php
namespace Civi\API\V4;
use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;

/**
 * @group headless
 */
class ReflectionUtilsTest extends \PHPUnit_Framework_TestCase implements HeadlessInterface, TransactionalInterface {

  public function setUpHeadless() {
    return \Civi\Test::headless()->installMe(__DIR__)->apply();
  }

  /**
   * Test that class annotations are returned across @inheritDoc
   */
  public function testGetDocBlockForClass() {
    $grandChild = new TestV4ReflectionGrandchild();
    $reflection = new \ReflectionClass($grandChild);
    $doc = ReflectionUtils::getCodeDocs($reflection);

    $this->assertEquals(TRUE, $doc['internal']);
    $this->assertEquals('Grandchild class', $doc['description']);

    $expectedComment = 'This is an extended description.

There is a line break in this description.

This is the base class.';

    $this->assertEquals($expectedComment, $doc['comment']);
  }

  /**
   * Test that property annotations are returned across @inheritDoc
   */
  public function testGetDocBlockForProperty() {
    $grandChild = new TestV4ReflectionGrandchild();
    $reflection = new \ReflectionClass($grandChild);
    $doc = ReflectionUtils::getCodeDocs($reflection->getProperty('foo'), 'Property');

    $this->assertEquals('This is the foo property.', $doc['description']);
    $this->assertEquals("In the child class, foo has been barred.\n\nIn general, you can do nothing with it.", $doc['comment']);
  }

}

/**
 * Class TestV4ReflectionBase
 *
 * This is the base class.
 *
 * @internal
 */
class TestV4ReflectionBase {
  /**
   * This is the foo property.
   *
   * In general, you can do nothing with it.
   *
   * @var array
   */
  public $foo = array();

}

/**
 * @inheritDoc
 */
class TestV4ReflectionChild extends TestV4ReflectionBase {
  /**
   * @inheritDoc
   *
   * In the child class, foo has been barred.
   */
  public $foo = array('bar' => 1);

}

/**
 * Grandchild class
 *
 * This is an extended description.
 *
 * There is a line break in this description.
 *
 * @inheritDoc
 */
class TestV4ReflectionGrandchild extends TestV4ReflectionChild {

}
