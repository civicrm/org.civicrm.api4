<?php

namespace Civi\Api4\Generic;

/**
 * Base class for DAO-based entities.
 */
abstract class DAOEntity extends AbstractEntity {

  /**
   * @return \Civi\Api4\Action\Get
   */
  public static function get() {
    return new \Civi\Api4\Action\Get(self::getEntityName());
  }

  /**
   * @return \Civi\Api4\Action\GetFields
   */
  public static function getFields() {
    return new \Civi\Api4\Action\GetFields(self::getEntityName());
  }

  /**
   * @return \Civi\Api4\Action\Create
   */
  public static function create() {
    return new \Civi\Api4\Action\Create(self::getEntityName());
  }

  /**
   * @return \Civi\Api4\Action\Update
   */
  public static function update() {
    return new \Civi\Api4\Action\Update(self::getEntityName());
  }

  /**
   * @return \Civi\Api4\Action\Delete
   */
  public static function delete() {
    return new \Civi\Api4\Action\Delete(self::getEntityName());
  }

  /**
   * @return \Civi\Api4\Action\Replace
   */
  public static function replace() {
    return new \Civi\Api4\Action\Replace(self::getEntityName());
  }

}
