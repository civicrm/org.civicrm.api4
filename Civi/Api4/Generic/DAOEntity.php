<?php

namespace Civi\Api4\Generic;

/**
 * Base class for DAO-based entities.
 */
abstract class DAOEntity extends AbstractEntity {

  /**
   * @return Action\DAOGet
   */
  public static function get() {
    return new Action\DAOGet(self::getEntityName());
  }

  /**
   * @return Action\DAOGetFields
   */
  public static function getFields() {
    return new Action\DAOGetFields(self::getEntityName());
  }

  /**
   * @return Action\DAOCreate
   */
  public static function create() {
    return new Action\DAOCreate(self::getEntityName());
  }

  /**
   * @return Action\DAOUpdate
   */
  public static function update() {
    return new Action\DAOUpdate(self::getEntityName());
  }

  /**
   * @return Action\DAODelete
   */
  public static function delete() {
    return new Action\DAODelete(self::getEntityName());
  }

  /**
   * @return Action\BasicReplace
   */
  public static function replace() {
    return new Action\BasicReplace(self::getEntityName());
  }

}
