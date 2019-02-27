<?php

namespace Civi\Api4;
use Civi\Api4\Generic\DAOEntity;

/**
 * Contacts - Individuals, Organizations, Households.
 *
 * This is the central entity in the CiviCRM database, and links to
 * many other entities (Email, Phone, Participant, etc.).
 *
 * Creating a new contact requires at minimum a name or email address.
 *
 * @package Civi\Api4
 */
class Contact extends DAOEntity {

  /**
   * @return \Civi\Api4\Action\Contact\Create
   */
  public static function create() {
    return new \Civi\Api4\Action\Contact\Create();
  }

  /**
   * @return \Civi\Api4\Action\Contact\Update
   */
  public static function update() {
    return new \Civi\Api4\Action\Contact\Update();
  }

}
