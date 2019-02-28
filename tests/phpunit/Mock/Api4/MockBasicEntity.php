<?php

namespace Civi\Api4;
require_once 'tests/phpunit/Mock/MockEntityDataStorage.php';

/**
 * MockBasicEntity entity.
 *
 * @package Civi\Api4
 */
class MockBasicEntity extends Generic\AbstractEntity {

  /**
   * @return Generic\Action\Basic\Get
   */
  public static function get() {
    return new Generic\Action\Basic\Get('MockBasicEntity', ['MockEntityDataStorage', 'get']);
  }

  /**
   * @return Generic\Action\Basic\Create
   */
  public static function create() {
    return new Generic\Action\Basic\Create('MockBasicEntity', ['MockEntityDataStorage', 'write']);
  }

  /**
   * @return Generic\Action\Basic\Update
   */
  public static function update() {
    return new Generic\Action\Basic\Update('MockBasicEntity', ['MockEntityDataStorage', 'write']);
  }

  /**
   * @return Generic\Action\Basic\Delete
   */
  public static function delete() {
    return new Generic\Action\Basic\Delete('MockBasicEntity', ['MockEntityDataStorage', 'delete']);
  }

  /**
   * @return Generic\Action\Basic\Replace
   */
  public static function replace() {
    return new Generic\Action\Basic\Replace('MockBasicEntity');
  }

}
