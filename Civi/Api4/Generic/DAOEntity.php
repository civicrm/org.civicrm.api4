<?php

namespace Civi\Api4\Generic;

/**
 * Base class for DAO-based entities.
 */
abstract class DAOEntity extends AbstractEntity {

  /**
   * @return DAOGetAction
   */
  public static function get() {
    return new DAOGetAction(self::getEntityName());
  }

  /**
   * @return DAOGetFieldsAction
   */
  public static function getFields() {
    return new DAOGetFieldsAction(self::getEntityName());
  }

  /**
   * @return DAOCreateAction
   */
  public static function create() {
    return new DAOCreateAction(self::getEntityName());
  }

  /**
   * @return DAOUpdateAction
   */
  public static function update() {
    return new DAOUpdateAction(self::getEntityName());
  }

  /**
   * @return DAODeleteAction
   */
  public static function delete() {
    return new DAODeleteAction(self::getEntityName());
  }

  /**
   * @return BasicReplaceAction
   */
  public static function replace() {
    return new BasicReplaceAction(self::getEntityName());
  }

}
