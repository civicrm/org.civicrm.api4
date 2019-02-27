<?php

namespace Civi\Api4;
use Civi\Api4\Generic\DAOEntity;

/**
 * GroupContact entity - link between groups and contacts.
 *
 * A contact can either be "Added" "Removed" or "Pending" in a group.
 * CiviCRM only considers them to be "in" a group if their status is "Added".
 *
 * @package Civi\Api4
 */
class GroupContact extends DAOEntity {

  /**
   * @return \Civi\Api4\Action\GroupContact\Create
   */
  public static function create() {
    return new \Civi\Api4\Action\GroupContact\Create();
  }

  /**
   * @return \Civi\Api4\Action\GroupContact\Update
   */
  public static function update() {
    return new \Civi\Api4\Action\GroupContact\Update();
  }

}
