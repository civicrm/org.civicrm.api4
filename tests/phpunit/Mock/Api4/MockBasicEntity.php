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
   * @return Generic\BasicGetAction
   */
  public static function get() {
    return new Generic\BasicGetAction('MockBasicEntity', __FUNCTION__, [self::STORAGE_CLASS, 'get']);
  }

  /**
   * @return Generic\BasicCreateAction
   */
  public static function create() {
    return new Generic\BasicCreateAction('MockBasicEntity', __FUNCTION__, [self::STORAGE_CLASS, 'write']);
  }

  /**
   * @return Generic\BasicUpdateAction
   */
  public static function update() {
    return new Generic\BasicUpdateAction('MockBasicEntity', __FUNCTION__, [self::STORAGE_CLASS, 'write']);
  }

  /**
   * @return Generic\BasicDeleteAction
   */
  public static function delete() {
    return new Generic\BasicDeleteAction('MockBasicEntity', __FUNCTION__, [self::STORAGE_CLASS, 'delete']);
  }

  /**
   * @return Generic\BasicReplaceAction
   */
  public static function replace() {
    return new Generic\BasicReplaceAction('MockBasicEntity', __FUNCTION__);
  }

}