<?php

namespace Civi\Api4;

/**
 * CustomGroup entity.
 *
 * @package Civi\Api4
 */
class CustomValue extends Generic\AbstractEntity {

  /**
   * @param string $customGroup
   * @return \Civi\Api4\Action\CustomValue\Get
   */
  public static function get($customGroup) {
    return new Action\CustomValue\Get($customGroup);
  }

  /**
   * @param string $customGroup
   * @return \Civi\Api4\Action\CustomValue\GetFields
   */
  public static function getFields($customGroup) {
    return new Action\CustomValue\GetFields($customGroup);
  }

  /**
   * @param string $customGroup
   * @return \Civi\Api4\Action\CustomValue\Create
   */
  public static function create($customGroup) {
    return new Action\CustomValue\Create($customGroup);
  }

  /**
   * @param string $customGroup
   * @return \Civi\Api4\Action\CustomValue\Update
   */
  public static function update($customGroup) {
    return new Action\CustomValue\Update($customGroup);
  }

  /**
   * @param string $customGroup
   * @return \Civi\Api4\Action\CustomValue\Delete
   */
  public static function delete($customGroup) {
    return new Action\CustomValue\Delete($customGroup);
  }

  /**
   * @param string $customGroup
   * @return \Civi\Api4\Action\CustomValue\Replace
   */
  public static function replace($customGroup) {
    return new \Civi\Api4\Action\CustomValue\Replace($customGroup);
  }

  /**
   * @inheritDoc
   */
  public static function permissions() {
    $entity = 'contact';
    $permissions = \CRM_Core_Permission::getEntityActionPermissions();

    // Merge permissions for this entity with the defaults
    return \CRM_Utils_Array::value($entity, $permissions, []) + $permissions['default'];
  }

}
