<?php

namespace Civi\Api4;
use Civi\Api4\Generic\DAOEntity;

/**
 * Navigation entity.
 *
 * @package Civi\Api4
 */
class Navigation extends DAOEntity {

  /**
   * @return \Civi\Api4\Action\Navigation\Get
   */
  public static function get() {
    return new \Civi\Api4\Action\Navigation\Get();
  }

}
