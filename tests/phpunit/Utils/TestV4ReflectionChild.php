<?php

namespace Civi\Test\API\V4\Utils;

use Civi\Test\API\V4\Utils;

/**
 * @inheritDoc
 */
class TestV4ReflectionChild extends Utils\TestV4ReflectionBase {
  /**
   * @inheritDoc
   *
   * In the child class, foo has been barred.
   */
  public $foo = array('bar' => 1);

}
