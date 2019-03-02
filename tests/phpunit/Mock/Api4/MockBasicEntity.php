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
   * @return Generic\Action\BasicGet
   */
  public static function get() {
    return new Generic\Action\BasicGet('MockBasicEntity', ['MockEntityDataStorage', 'get']);
  }

  /**
   * @return Generic\Action\BasicCreate
   */
  public static function create() {
    return new Generic\Action\BasicCreate('MockBasicEntity', ['MockEntityDataStorage', 'write']);
  }

  /**
   * @return Generic\Action\BasicUpdate
   */
  public static function update() {
    return new Generic\Action\BasicUpdate('MockBasicEntity', ['MockEntityDataStorage', 'write']);
  }

  /**
   * @return Generic\Action\BasicDelete
   */
  public static function delete() {
    return new Generic\Action\BasicDelete('MockBasicEntity', ['MockEntityDataStorage', 'delete']);
  }

  /**
   * @return Generic\Action\BasicReplace
   */
  public static function replace() {
    return new Generic\Action\BasicReplace('MockBasicEntity');
  }

}
