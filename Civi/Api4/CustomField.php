<?php

namespace Civi\Api4;

/**
 * CustomField entity.
 *
 * @package Civi\Api4
 */
class CustomField extends Generic\DAOEntity {

  /**
   * @return \Civi\Api4\Action\CustomField\Create
   */
  public static function create() {
    return new \Civi\Api4\Action\CustomField\Create(__CLASS__, __FUNCTION__);
  }

}
