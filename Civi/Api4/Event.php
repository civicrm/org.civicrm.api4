<?php

namespace Civi\Api4;

/**
 * Event entity.
 *
 * @package Civi\Api4
 */
class Event extends Generic\DAOEntity {

  /**
   * @return \Civi\Api4\Action\Event\Create
   */
  public static function create() {
    return new \Civi\Api4\Action\Event\Create(__CLASS__, __FUNCTION__);
  }

}
