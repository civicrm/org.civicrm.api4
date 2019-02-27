<?php

namespace Civi\Api4;
use Civi\Api4\Generic\DAOEntity;

/**
 * Address Entity.
 *
 * This entity holds the address informatiom of a contact. Each contact may hold
 * one or more addresses but must have different location types respectively.
 *
 * Creating a new address requires at minimum a contact's ID and location type ID
 *  and other attributes (although optional) like street address, city, country etc.
 *
 * @package Civi\Api4
 */
class Address extends DAOEntity {

  /**
   * @return \Civi\Api4\Action\Address\Create
   */
  public static function create() {
    return new \Civi\Api4\Action\Address\Create();
  }

  /**
   * @return \Civi\Api4\Action\Address\Update
   */
  public static function update() {
    return new \Civi\Api4\Action\Address\Update();
  }

}
