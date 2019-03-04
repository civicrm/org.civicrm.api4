<?php

namespace Civi\Api4;

/**
 * MockBasicEntity entity.
 *
 * @package Civi\Api4
 */
class MockBasicEntity extends Generic\AbstractEntity {

  const STORAGE_CLASS = '\\Civi\\Test\\Api4\\Mock\\MockEntityDataStorage';

  /**
   * @return Generic\Action\BasicGet
   */
  public static function get() {
    return new Generic\Action\BasicGet('MockBasicEntity', [self::STORAGE_CLASS, 'get']);
  }

  /**
   * @return Generic\Action\BasicCreate
   */
  public static function create() {
    return new Generic\Action\BasicCreate('MockBasicEntity', [self::STORAGE_CLASS, 'write']);
  }

  /**
   * @return Generic\Action\BasicUpdate
   */
  public static function update() {
    return new Generic\Action\BasicUpdate('MockBasicEntity', [self::STORAGE_CLASS, 'write']);
  }

  /**
   * @return Generic\Action\BasicDelete
   */
  public static function delete() {
    return new Generic\Action\BasicDelete('MockBasicEntity', [self::STORAGE_CLASS, 'delete']);
  }

  /**
   * @return Generic\Action\BasicReplace
   */
  public static function replace() {
    return new Generic\Action\BasicReplace('MockBasicEntity');
  }

  /**
   * @return Generic\Action\BasicGet
   */
  public static function invalid() {
    // This is expected to fail because the entity name is a required param when constructing basic actions directly.
    return new Generic\Action\BasicGet();
  }

}
