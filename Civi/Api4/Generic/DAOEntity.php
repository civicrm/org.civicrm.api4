<?php

namespace Civi\Api4\Generic;

/**
 * Base class for DAO-based entities.
 */
abstract class DAOEntity extends AbstractEntity {

  /**
   * @return \Civi\Api4\Generic\Action\DAO\Get
   */
  public static function get() {
    return new Action\DAO\Get(self::getEntityName());
  }

  /**
   * @return \Civi\Api4\Generic\Action\DAO\GetFields
   */
  public static function getFields() {
    return new Action\DAO\GetFields(self::getEntityName());
  }

  /**
   * @return \Civi\Api4\Generic\Action\DAO\Create
   */
  public static function create() {
    return new Action\DAO\Create(self::getEntityName());
  }

  /**
   * @return \Civi\Api4\Generic\Action\DAO\Update
   */
  public static function update() {
    return new Action\DAO\Update(self::getEntityName());
  }

  /**
   * @return \Civi\Api4\Generic\Action\DAO\Delete
   */
  public static function delete() {
    return new Action\DAO\Delete(self::getEntityName());
  }

  /**
   * @return \Civi\Api4\Generic\Action\Basic\Replace
   */
  public static function replace() {
    return new Action\Basic\Replace(self::getEntityName());
  }

}
