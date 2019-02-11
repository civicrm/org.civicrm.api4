<?php

namespace Civi\Api4;
use Civi\Api4\Generic\AbstractEntity;
use Civi\Api4\Generic\AbstractAction;
use Civi\API\Exception\NotImplementedException;

/**
 * CustomGroup entity.
 *
 * @package Civi\Api4
 *
 * @method static \Civi\Api4\Action\CustomValue\Get get(string $customGroupName)
 * @method static \Civi\Api4\Action\CustomValue\GetFields getFields(string $customGroupName)
 * @method static \Civi\Api4\Action\CustomValue\Create create(string $customGroupName)
 * @method static \Civi\Api4\Action\CustomValue\Update update(string $customGroupName)
 * @method static \Civi\Api4\Action\CustomValue\Delete delete(string $customGroupName)
 * @method static \Civi\Api4\Action\CustomValue\Replace replace(string $customGroupName)
 */
class CustomValue extends AbstractEntity {

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
